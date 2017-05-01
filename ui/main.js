//
// This is the main app for the aprs module
//
function qruqsp_aprs_main() {
    //
    // The panel to list the entry
    //
    this.menu = new Q.panel('entry', 'qruqsp_aprs_main', 'menu', 'mc', 'full', 'sectioned', 'qruqsp.aprs.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':5,
            'cellClasses':[''],
            'hint':'Search entry',
            'noData':'No entry found',
            },
        'entries':{'label':'Latest Entries', 'type':'simplegrid', 'num_cols':5,
            'noData':'No entry',
            'headerValues':['From', 'Heard', 'Time', 'Level', 'Comments'],
            'sortable':'yes',
            'sortTypes':['text', 'text', 'text', 'text', 'text'],
            'addTxt':'Add APRS Entry',
            'addFn':'Q.qruqsp_aprs_main.entry.open(\'Q.qruqsp_aprs_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            Q.api.getJSONBgCb('qruqsp.aprs.entrySearch', {'station_id':Q.curStationID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                Q.qruqsp_aprs_main.menu.liveSearchShow('search',null,Q.gE(Q.qruqsp_aprs_main.menu.panelUID + '_' + s), rsp.entries);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return this.cellValue(s, i, j, d);
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'Q.qruqsp_aprs_main.entry.open(\'Q.qruqsp_aprs_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'entries' || s == 'search' ) {
            switch(j) {
                case 0: return d.from_call_sign;
                case 1: return d.heard_call_sign;
                case 2: return d.utc_of_traffic;
                case 3: return d.level;
                case 4: return d.comment;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'entries' ) {
            return 'Q.qruqsp_aprs_main.entry.open(\'Q.qruqsp_aprs_main.menu.open();\',\'' + d.id + '\',Q.qruqsp_aprs_main.entry.nplist);';
        }
    }
    this.menu.open = function(cb) {
        Q.api.getJSONCb('qruqsp.aprs.entryList', {'station_id':Q.curStationID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_aprs_main.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to display APRS Entry
    //
    this.entry = new Q.panel('APRS Entry', 'qruqsp_aprs_main', 'entry', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.aprs.main.entry');
    this.entry.data = null;
    this.entry.entry_id = 0;
    this.entry.sections = {
        'general':{'label':'APRS Entry', 'aside':'yes', 'list':{
            'decoder':{'label':'Decoder'},
            'channel':{'label':'Channel'},
            'utc_of_traffic':{'label':'Time'},
            'from_call_sign':{'label':'From Call Sign'},
            'from_call_suffix':{'label':'From Call Suffix'},
            'heard_call_sign':{'label':'Heard Call Sign'},
            'heard_call_suffix':{'label':'Heard Call Suffix'},
            }},
        'comment':{'label':'Comment', 'type':'html'},
        'location':{'label':'Location', 'list':{
            'latitude':{'label':'Latitude'},
            'longitude':{'label':'Longitude'},
            'speed':{'label':'Speed'},
            'course':{'label':'Course'},
            'altitude':{'label':'Altitude'},
            }},
        'details1':{'label':'', 'aside':'yes', 'list':{
            'level':{'label':'Level'},
            'error':{'label':'Error'},
            'dti':{'label':'DTI'},
            'name':{'label':'Name'},
            'symbol':{'label':'Symbol'},
            }},
        'details2':{'label':'', 'list':{
            'frequency':{'label':'Frequency'},
            'offset':{'label':'Offset'},
            'tone':{'label':'Tone'},
            'system':{'label':'System'},
            'status':{'label':'Status'},
            'telemetry':{'label':'Telemetry'},
            }},
    }
    this.entry.listLabel = function(s, i, d) { return d.label; }
    this.entry.listValue = function(s, i, d) { return this.data[i]; }
    this.entry.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.aprs.entryGet', {'station_id':Q.curStationID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_aprs_main.entry;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.entry.addClose('Back');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = Q.createContainer(ap, 'qruqsp_aprs_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
