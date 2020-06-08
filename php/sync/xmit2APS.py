#! /usr/bin/python
#
# Transmit the schedule to the APS
#
# Copyright (C) 2010-2012 by Keith Briter
# All rights reserved.
#
from suds.client import Client
from suds.wsse import Security, UsernameToken

import pg               # our database
import getpass          # in case the environment doesn't have it
import datetime         # timestamp conversions
import sys              # exit, stderr, and flush on stdout
import os               # for the environment

class Xmit2APS:
    db  = None          # our connection to the database
    
    def __init__( self, username, password, theRun):
        self.username = username
        self.password = password
        self.theRun   = theRun

        self.percentDone = 0.0
        self.lastPercentDone = self.percentDone

        self.db = pg.connect( dbname="ls", host="contrabass.ls-cat.org", user="lsuser" )

    def pytime( self, ts):
        return datetime.datetime( int(ts[0:4]), int(ts[5:7]), int(ts[8:10]), int(ts[11:13]), int(ts[14:16]))


    def setSoapHeader( self, client, username, password):
        """
        This function takes a suds.  Client object, along with a
        username and password, and creates a WS Security header to be
        applied to all future SOAP requests.
        """

        #
        # setSoapHeader from APS example code pybeamsched.py
        #
        security = Security()
        token = UsernameToken(username, password)
        token.setcreated()
        security.tokens.append(token)
        client.set_options(wsse=security)

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

    def findBeamtimeRequestsByBeamline( self, beamlineName, runName, bssc):
        """
        Find list of all beamtime requests available for scheduling
        on given beamlineName for given runName.  Returns array of
        beamtime requests.
        """
        #
        # findBeamtimeRequestByBeamline from APS example code pybeamsched.py
        #

        try:
            result = bssc.service.findBeamtimeRequestsByBeamline(beamlineName, runName)
        except Exception, soapFault:
            self.percentDone = -1
            self.outs( soapFault)
            sys.exit()

        if len(result) == 0:
            return []

        btrArray = result.beamtimeRequest
        return btrArray


    def run( self, asRun=False):


        # URLs to WSDL service definitions
        # One for the beamline schedule service
        beamlineScheduleServiceURL = 'https://schedule.aps.anl.gov/beamschedds/springws/beamlineScheduleService/beamlineScheduleWebService.wsdl?yyyxx'

        # Make a SOAP client
        bc = Client(beamlineScheduleServiceURL)
        #bc.options.cache.setduration(seconds=10)
        self.setSoapHeader(bc, self.username, self.password)

        expStations = [{"beamlineName" : "21-ID-D", "stationName" : "D", "stnKey" : 1, "cal" : "id21d.cal"},
                       # {"beamlineName" : "21-ID-E", "stationName" : "E", "stnKey" : 2, "cal" : "id21e.cal"},
                       {"beamlineName" : "21-ID-F", "stationName" : "F", "stnKey" : 3, "cal" : "id21f.cal"},
                       {"beamlineName" : "21-ID-G", "stationName" : "G", "stnKey" : 4, "cal" : "id21g.cal"}
                       ]


        #
        # Get the beamtime requests
        #
        # 0 - 5 %
        #
        self.percentDone = 0.0
        self.outs( "Getting BTRs..")
        doneIncrement = 5.0/len(expStations)
        btrArray = []
        for es in expStations:
            bn = es["beamlineName"]
            ta = self.findBeamtimeRequestsByBeamline( bn, self.theRun, bc)
            for t in ta:
                btrArray.append(t)
            
            self.percentDone += doneIncrement
            self.outs()

        #
        # find the beamtime requests (btr) from the static proposal numbers
        # for the member institutions to use when no ESAF has yet been filed
        #
        # 5 - 10 %
        #
        self.percentDone = 5.0
        self.outs("Match to standing proposals")

        qs = "select silabel, siproposal, siactivitytype, sibackground from lsched.schedinsts"
        qr = self.db.query( qs)
        standingbtrs = {}

        for r in qr.dictresult():
            if r["siproposal"] != None:
                for b in btrArray:
                    if b.proposal.id == int(r["siproposal"]):
                        t = {}
                        t["btr"] = b
                        t["activityType"] = r["siactivitytype"]
                        t["displayColor"] = r["sibackground"]
                        standingbtrs[r["silabel"]] = t
                        break

        #
        # get proposal numbers and btrs for the esaf's that have (or are about to) run
        #
        # 10 - 15 %
        #
        self.percentDone = 10.0
        self.outs( "Matching ESAFS")

        qs = "select esaf, proposal, ptype from lsched.esaf2proposal('%s') where proposal >0" % (self.theRun)
        qr = self.db.query( qs)
        esafs = {}
        for r in qr.dictresult():
            e = {}
            e["ptype"] = r["ptype"]
            p = e["proposal"] = int(r["proposal"])
            ba = []
            for b in btrArray:
                #
                # a proposal may have many btrs
                #
                if b.proposal.id == p:
                    ba.append(b)
            e["btrs"] = ba
            esafs[int(r["esaf"])] = e

        #
        # Cylcle through the beamlines
        #
        #
        # 15 - 95%
        #
        # so, 80% divided by nstations 
        #          1/2 for scheduled activities
        #          1/2 for as-run activities (if requested)
        #
        
        self.percentDone = 15.0
        self.outs( "Creating Activities")

        nstns = len(expStations)
        

        for es in expStations:
            bn = es["beamlineName"]
            cal = es["cal"]
            stationName = es["stationName"]
            stnKey = es["stnKey"]

            self.outs(stationName)

            #
            # reset the aps's version of this station's schedule
            #
            bc.service.deleteBeamlineSchedule( bn, self.theRun)
            bc.service.initializeBeamlineSchedule( bn, self.theRun)


            #
            # Now walk through the calender and add activities
            #
            activityElements = bc.factory.create('activityElementArray')
            
            qs = "select * from %s where crunname='%s' order by cbegin asc" % (cal, self.theRun)
            qr = self.db.query( qs)

            doneIncrement = 80.0 / nstns / (qr.ntuples() + 1.0)
            if asRun:
                doneIncrement /= 2.0

            ael = []
            for r in qr.dictresult():
                if r["cend"] == None or r["cbegin"] == None:
                   continue;
                ae = bc.factory.create('activityElement')

                ae.clientFkId = int( r["ckey"])     # an id for use to reference the LS-CAT schedule
                ae.startTime = self.pytime( r["cbegin"])
                ae.endTime   = self.pytime( r["cend"])
                ae.stationName = stationName
                ae.comment     = str(r["cstaff"])
                if standingbtrs.has_key( r["clabel"]):
                    found = True
                    ae.activityTypeName = standingbtrs[r["clabel"]]["activityType"]
                    ae.activityName = ae.activityTypeName
                    ae.beamtimeRequestId = standingbtrs[r["clabel"]]["btr"].id
                    ae.displayColor = standingbtrs[r["clabel"]]["displayColor"]

                else:
                    found = False
                    foundStartDate = None
                    for b in btrArray:
                        try:
                            if int(r["clabel"]) == b.proposal.id:
                                found = True
                                break
                        except ValueError:
                            # probably clabel wasn't an integer, or b had no proposal: bail regardless
                            #
                            break
                    if found:
                        # if it's not in our list of member proposals then it must be a GUP
                        #
                        ae.activityTypeName = "GUP"
                        ae.activityName = "GUP"
                        ae.beamtimeRequestId = b.id
                        
                if found:
                    # look for the corresponding ESAF
                    gotit = False
                    for e in esafs.keys():
                        for b in esafs[e]["btrs"]:
                            if b.id == ae.beamtimeRequestId:
                                ae.esafId = e
                                gotit = True
                                break
                        if gotit:
                            break;
                    currentBtrId = ae.beamtimeRequestId

                    ael.append(ae)
                    
                else:
                    currentBtrId = None

                self.percentDone += doneIncrement
                self.outs()

                # Now look at the ESAF's that ran that day
                #
                if asRun:
                    qs2 = "select distinct dsesaf from px.datasets where dscreatets>='%s' and dscreatets<='%s' and dsstn=%d and dsesaf is not null" % (r["cbegin"], r["cend"], stnKey)
                    qr2 = self.db.query( qs2)

                    doneIncrement = 80.0 / nstns / 2.0 / (qr2.ntuples() + 1)

                    for r2 in qr2.dictresult():
                        esaf = int( r2["dsesaf"])
                        if not esafs.has_key(esaf):
                            continue
                        proposal = esafs[esaf]["proposal"]
                        btr = None
                        for b in esafs[esaf]["btrs"]:
                            #
                            # TODO: match the best btr to this slot
                            # for now just take the last one or the one that we've just used
                            #
                            btr = b
                            if btr.id == currentBtrId:
                                break
                        #
                        # ignore this one if we've already added this activity
                        #
                        if btr==None or btr.id == currentBtrId:
                            break;
        
                        ae = bc.factory.create('activityElement')
        
                        ae.clientFkId = 0     # No good id...
                        ae.startTime = self.pytime( r["cbegin"])
                        ae.endTime   = self.pytime( r["cend"])
                        ae.stationName = stationName
                        ae.activityTypeName = esafs[esaf]["ptype"]
                        ae.activityName = esafs[esaf]["ptype"]
                        ae.beamtimeRequestId = btr.id
                        ae.esafId = esaf
                        ael.append(ae)

                        self.percentDone += doneIncrement
                        self.outs()

            activityElements.activityElement = ael

            self.outs( "transmitting")

            try:
                bc.service.addActivitiesToSchedule( self.theRun, bn, activityElements)
            except Exception, soapFault:
                self.percentDone = -1
                self.outs("Exception soapFault: %s" % (soapFault))
                sys.exit()

            self.outs("Sent")

        self.percentDone = 100
        self.outs("Finished sending to APS")


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

    x = Xmit2APS( username, password, theRun)
    x.run( asRun=False)
