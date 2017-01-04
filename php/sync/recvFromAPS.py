#! /usr/bin/python
#
# Receive the schedule from the APS
#
# Copyright (C) 2010 by Keith Briter
# All rights reserved.
#

from suds.client import Client
from suds.wsse import Security, UsernameToken
from suds import WebFault

import pg               # our database
import getpass          # in case no password was sent via env
import datetime         # fix up crazy SOAP time stamps
import sys              # argv
import os               # environment support
import pytz             # fix up crazy SOAP time stamp time zone
import urllib           # quote_plus == escape (flex) == rawurlencode (php)

class Staff:
    db        = None
    theRun    = None
    badge     = None
    station   = None
    startTime = None
    endTime   = None

    def __init__( self, db, theRun, station, startTime, endTime, badge):
        self.db        = db
        self.theRun    = theRun
        self.station   = station
        self.startTime = startTime
        self.endTime   = endTime
        self.badge     = badge
        self.qs = "select lsched.setstaff( '%s', '%s', '%s'::timestamptz, '%s'::timestamptz, %d) as ss" % (self.theRun, self.station, self.startTime, self.endTime, self.badge)

    def setFromAPS( self):
        qr = self.db.query( self.qs)
        return qr.dictresult()[0]["ss"]

    def __str__( self):
        return self.qs;

class Activity:
    db         = None
    theRun     = None   # text ("2011-1", for example)
    clientFkId = None   # bigint: key on the calendar object, for a given station clientFkId is unique.  0 means this activity has never been seen by us
    proposal   = None   # int:  The APS proposal number (79227, for example)
    theStn     = None   # text: the short station name: D, E, F, or G
    startTime  = None   # text: the timestamp of the start time
    endTime    = None   # text: the timestamp of the end time
    activityKey= None   # int: the APS activity key
    exists     = None   # this clientFkId is present in the database
    same       = None   # Not only is it present, but it is the same
    willReplace= None   # indicate that this will replace an existing non-free, non-rapid entry
    comment    = None   # if staff initials appear first then assign this person to this trip
    qs         = None   # Our current query string

    def __init__(self, db, theRun, activityKey, clientFkId, proposal, theStn, startTime, endTime, comment):
        self.db          = db
        self.theRun      = theRun
        self.clientFkId  = clientFkId
        self.proposal    = proposal
        self.theStn      = theStn
        self.startTime   = startTime
        self.endTime     = endTime
        self.activityKey = activityKey
        self.comment     = comment        # if this is staff initials then set the staff for this activity
        self.exists      = False
        self.same        = False
        self.willReplace = False
        self.qs = "select lsched.addFromAPS( '%s', %d, %d, %d, '%s', '%s', '%s', '%s') as afa" % (self.theRun, self.activityKey, self.clientFkId, self.proposal, self.theStn, self.startTime, self.endTime, self.db.escape_string(self.comment))

        if self.clientFkId != 0:
            qs = "select 1 as test from lsched.anycal() where crunname='%s' and cstn='21-ID-%s' and ckey=%d" % (self.theRun, self.theStn, self.clientFkId)
            qr = self.db.query( qs)
            if qr.ntuples() == 1:
                self.exists = True
            
            qs = "select 1 as test from lsched.anycal() where crunname='%s' and cstn='21-ID-%s' and ckey=%d and cbegin='%s'::timestamptz and cend='%s'::timestamptz" % (self.theRun, self.theStn, self.clientFkId, self.startTime, self.endTime)
            qr = self.db.query( qs)
            if qr.ntuples() == 1:
                self.same = True
        
            if not self.same:
                qs = "select 1 as test from lsched.anycal() where crunname='%s' and cstn='21-ID-%s' and ckey!=%d and cbegin='%s'::timestamptz and cend='%s'::timestamptz and not (clabel='RAPID' or clabel='FREE')" % (self.theRun, self.theStn, self.clientFkId, self.startTime, self.endTime)
                qr = self.db.query( qs)
                if qr.ntuples() == 1:
                    self.willReplace = True
            

    def addFromAPS( self):
        qr = self.db.query( self.qs)
        return qr.dictresult()[0]["afa"]

    def __str__( self):
        return self.qs



def Activitycmp( a, b):
    if a.startTime == b.startTime:
        return 0
    if a.startTime < b.startTime:
        return -1
    return 0



class ReceiveFromAPS:
    db = None           # Our database connection
    bc = None           # beamline scheduling client

    def setSoapHeader(self, client, username, password):
        """This function takes a suds.  Client object, along with a username and
        password, and creates a WS Security header to be applied to all
        future SOAP requests."""
        #
        # setSoapHeader from APS example code pybeamsched.py
        #
        security = Security()
        token = UsernameToken(username, password)
        token.setcreated()
        security.tokens.append(token)
        client.set_options(wsse=security)

    def correctTimezone( self, dateTime):
        """Create a time zone with the correct information and apply it
        to the datetime.  For some reason SOAP screws up the timestamps
        without this bit of a kludge."""

        # this bug appears to be fixed 150827
        #
        return dateTime

        dateTime = dateTime.replace(tzinfo=self.chicago)
        dateTime = self.chicago.normalize(dateTime)
    
        now = datetime.datetime.now().replace(tzinfo=self.chicago)
        now = self.chicago.normalize(now)


        #
        # I think the datetimes created by suds are screwed up and need to be corrected by 1 hour
        # if we make the suds call during daylight savings time.
        #
        # I might be wrong in which case comment out the now.dst line and uncomment out the fixed correction or, perhaps, don't correct things.
        # We'll know November 2...
        #

        #dateTime = dateTime - now.dst()
        dateTime = dateTime - datetime.timedelta( 0,0,0,0,0,1)
        return dateTime


    def outs( self, message=""):
        """
        Simple output function to make it easy for the controlling script to parse.
          self.percentDone should be -1 on error, 0-100 for the percent complete.
          message will likely be a progress bar message or an error message.
        """
        tmp = int(self.percentDone)
        if message != "" or tmp > self.lastPercentDone or tmp == -1:
            # print >>sys.stderr, tmp, message
            print "%d %s" % (tmp, message)
            self.lastPercentDone = tmp
        sys.stdout.flush()

    def __init__( self, username, password, theRun):
        """Make connections to the database and SOAP server."""


        self.theRun   = theRun
        self.db = pg.connect( dbname="ls", host="contrabass.ls-cat.org", user="lsuser" )
        self.chicago  = pytz.timezone('US/Central')

        #
        # 0 - 4 percent of the time starting up
        #
        self.percentDone = 0
        self.lastPercentDone = self.percentDone
        self.outs( "Starting...")

        # URLs to WSDL service definitions
        # One for the beamline schedule service
        beamlineScheduleServiceURL = 'https://schedule.aps.anl.gov/beamschedds/springws/beamlineScheduleService/beamlineScheduleWebService.wsdl?yyyxx'

        #
        # And one for the run schedule service (Really, all this does
        # is give a list of run names/dates) Run names are trivial to
        # generate and the dates are better gotten from the beamline
        # schedule since we can purge the machine studies off each end
        # and not clutter our schedule with irrelevant weeks
        #
        # runScheduleServiceURL = 'https://schedule.aps.anl.gov/beamschedds/springws/runScheduleService/runScheduleWebService.wsdl?yyxx'


        self.percentDone = 1
        self.outs( "Connecting...")

        # Make a SOAP client
        self.bc = Client(beamlineScheduleServiceURL)
        #self.bc.options.cache.setduration(seconds=10)
        self.setSoapHeader(self.bc, username, password)

        self.percentDone = 2
        self.outs( "Connected")

        #
        # the experimental stations.  One can add a layer of indirection by adding some columns to px._config and
        # replace this static definition with a query.
        #
        self.expStations = [{"beamlineName" : "21-ID-D", "stationName" : "D", "stnKey" : 1, "cal" : "id21d.cal"},
                            # {"beamlineName" : "21-ID-E", "stationName" : "E", "stnKey" : 2, "cal" : "id21e.cal"},
                            {"beamlineName" : "21-ID-F", "stationName" : "F", "stnKey" : 3, "cal" : "id21f.cal"},
                            {"beamlineName" : "21-ID-G", "stationName" : "G", "stnKey" : 4, "cal" : "id21g.cal"}
                            ]
        

    def run( self):
        """Download the schedule from the APS and install it at
        LS-CAT.  This routine forces schedule transitions at the start
        and end times of each activity, frees all the time in that
        slot, splices the slots together, and assigns the time as
        indicated in the schedule.  Activities are first sorted by
        start time to deal with the problem that the APS allows
        overlapping activities while we do not and the APS has a bug
        that does not allow us to correctly set the end time."""

        #
        # 5 - 100% divided into nstations divided into 3 sections
        #
        percentDone = 5

        nstns = len(self.expStations)

        self.db.query("begin");
        for e in self.expStations:
            
            try:
                result = self.bc.service.findBeamlineSchedule( e["beamlineName"], self.theRun)
            except WebFault:
                self.percentDone = -1
                self.outs( "Username/Password OK?")
                sys.exit(1)
                pass

            nactivities = len(result.activities.activity)+1      # add 1 just in case there are no activities
            doneIncrement = 95.0/nstns/3.0/nactivities
            # self.outs( "doneIncrement: %f, nactivities: %d" % (doneIncrement, nactivities))
            self.outs( e["beamlineName"])

            updates = []
            staffs  = []
            for a in result.activities.activity:
                self.percentDone += doneIncrement
                if a.activityType.systemActivityFlag == "true":
                    continue

                self.outs()

                if a.activityType.activityTypeName == "staff support":
                    startTime = self.correctTimezone( a.startTime)
                    endTime = self.correctTimezone( a.endTime)
                    sd = Staff( self.db, self.theRun, e["stationName"], startTime, endTime, int(a.staffSupport.badgeNumber))
                    staffs.append(sd)

                else:
                    ud = None
                    try:
                        if a.activityType.activityTypeName == "beamline start-up":
                            proposal = 24155
                        else:
                            proposal = int(a.beamtimeRequest.proposal.id)

                        clientFkId = int( a.clientFkId)
                        startTime = self.correctTimezone( a.startTime)
                        endTime   = self.correctTimezone( a.endTime)
                        try:
                            comment = a.comment
                        except AttributeError:
                            comment = ""

                        ud = Activity( self.db, self.theRun, a.id,  clientFkId, proposal, e["stationName"], startTime, endTime, comment);
                        updates.append( ud )

                    except AttributeError:
                        if ud != None:
                            print ud
                        print "AttributeError from this activity:"
                        print a
                        print a.comment
                    
            #
            # We sort things by start time to get around an APS bug that allows us to set the start time to within an hour but
            # forces the end time to be a multiple of 8 hours afterwards.  This will truncate the offending end time correctly if sorted first
            #
            updates.sort(Activitycmp)

            #
            #
            if len(updates) <= 0:
                self.percentDone += 95.0/nstns/3.0
            else:
                doneIncrement = 95.0/nstns/3.0/len(updates)
                # self.outs( "doneIncrement: %f, nupdates: %d" % (doneIncrement, len(updates)))

                self.db.query( "select lsched.freeAPSactivities( '%s', '%s')" % (self.theRun, e["stationName"]))
                for u in updates:
                    u.addFromAPS( )
                    self.percentDone += doneIncrement
                    self.outs()

            staffs.sort(Activitycmp)

            #
            #
            if len(staffs) <= 0:
                self.percentDone += 95.0/nstns/3.0
            else:
                doneIncrement = 95.0/nstns/3.0/len(staffs)
                # self.outs( "doneIncrement: %f, nstaffs: %d" % (doneIncrement, len(staffs)))
                for s in staffs:
                    s.setFromAPS( )
                    self.percentDone += doneIncrement
                    self.outs()

        self.db.query("commit");
        self.percentDone = 100
        self.outs( "recvFromAPS Done")


if __name__ == "__main__":
    
    if os.environ.has_key("theUserName"):
        username = os.environ["theUserName"]
    else:
        username = raw_input('Enter your APS badge number: ')

    if os.environ.has_key("thePassword"):
        password = os.environ["thePassword"]
    else:
        password = getpass.getpass('Password: ')
    
    if os.environ.has_key("theRun"):
        theRun = os.environ["theRun"]
    else:
        theRun = raw_input( 'Run Name: ')


    rfa = ReceiveFromAPS( username, password, theRun)
    rfa.run()
