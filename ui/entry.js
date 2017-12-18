    //
    // The panel to list the entry
    //
    this.entries = new M.panel('entry', 'qruqsp_aprs_main', 'entries', 'mc', 'medium', 'sectioned', 'qruqsp.aprs.main.entries');
    this.entries.data = {};
    this.entries.nplist = [];
    this.entries.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':1,
            'cellClasses':[''],
            'hint':'Search entry',
            'noData':'No entry found',
            },
        'entries':{'label':'APRS Entry', 'type':'simplegrid', 'num_cols':1,
            'noData':'No entry',
            'addTxt':'Add APRS Entry',
            'addFn':'M.qruqsp_aprs_main.edit.open(\'M.qruqsp_aprs_main.entries.open();\',0,null);'
            },
    }
    this.entries.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('qruqsp.aprs.entrySearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.qruqsp_aprs_main.entries.liveSearchShow('search',null,M.gE(M.qruqsp_aprs_main.entries.panelUID + '_' + s), rsp.entries);
                });
        }
    }
    this.entries.liveSearchResultValue = function(s, f, i, j, d) {
        return d.name;
    }
    this.entries.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.qruqsp_aprs_main.entry.open(\'M.qruqsp_aprs_main.entries.open();\',\'' + d.id + '\');';
    }
    this.entries.cellValue = function(s, i, j, d) {
        if( s == 'entries' ) {
            switch(j) {
                case 0: return d.name;
            }
        }
    }
    this.entries.rowFn = function(s, i, d) {
        if( s == 'entries' ) {
            return 'M.qruqsp_aprs_main.entry.open(\'M.qruqsp_aprs_main.entries.open();\',\'' + d.id + '\',M.qruqsp_aprs_main.entry.nplist);';
        }
    }
    this.entries.open = function(cb) {
        M.api.getJSONCb('qruqsp.aprs.entryList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_aprs_main.entries;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.entries.addClose('Back');

    //
    // The panel to display APRS Entry
    //
    this.entry = new M.panel('APRS Entry', 'qruqsp_aprs_main', 'entry', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.aprs.main.entry');
    this.entry.data = null;
    this.entry.entry_id = 0;
    this.entry.sections = {
        'general':{'label':'', 'fields':{
            'decoder':{'label':'Decoder', 'type':'text'},
            'channel':{'label':'Channel', 'type':'text'},
            'utc_of_traffic':{'label':'Time', 'type':'date'},
            'from_call_sign':{'label':'From Call Sign', 'type':'text'},
            'from_call_suffix':{'label':'From Call Suffix', 'type':'text'},
            'heard_call_sign':{'label':'Heard Call Sign', 'type':'text'},
            'heard_call_suffix':{'label':'Heard Call Suffix', 'type':'text'},
            'level':{'label':'Level', 'type':'text'},
            'error':{'label':'Error', 'type':'text'},
            'dti':{'label':'DTI', 'type':'text'},
            'name':{'label':'Name', 'type':'text'},
            'symbol':{'label':'Symbol', 'type':'text'},
            'latitude':{'label':'Latitude', 'type':'text'},
            'longitude':{'label':'Longitude', 'type':'text'},
            'speed':{'label':'Longitude', 'type':'text'},
            'course':{'label':'Course', 'type':'text'},
            'altitude':{'label':'Altitude', 'type':'text'},
            'frequency':{'label':'Frequency', 'type':'text'},
            'offset':{'label':'Offset', 'type':'text'},
            'tone':{'label':'Tone', 'type':'text'},
            'system':{'label':'System', 'type':'text'},
            'status':{'label':'Status', 'type':'text'},
            'telemetry':{'label':'Telemetry', 'type':'text'},
            }},
        '_comment':{'label':'Comment', 'fields':{
            'comment':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
    }
    this.entry.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.aprs.entryGet', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_aprs_main.entry;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.entry.addButton('edit', 'Edit', 'M.qruqsp_aprs_main.edit.open(\'M.qruqsp_aprs_main.entry.open();\',M.qruqsp_aprs_main.entry.entry_id);');
    this.entry.addClose('Back');

    //
    // The panel to edit APRS Entry
    //
    this.edit = new M.panel('APRS Entry', 'qruqsp_aprs_main', 'edit', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.aprs.main.edit');
    this.edit.data = null;
    this.edit.entry_id = 0;
    this.edit.nplist = [];
    this.edit.sections = {
        'general':{'label':'', 'fields':{
            'decoder':{'label':'Decoder', 'type':'text'},
            'channel':{'label':'Channel', 'type':'text'},
            'utc_of_traffic':{'label':'Time', 'type':'date'},
            'from_call_sign':{'label':'From Call Sign', 'type':'text'},
            'from_call_suffix':{'label':'From Call Suffix', 'type':'text'},
            'heard_call_sign':{'label':'Heard Call Sign', 'type':'text'},
            'heard_call_suffix':{'label':'Heard Call Suffix', 'type':'text'},
            'level':{'label':'Level', 'type':'text'},
            'error':{'label':'Error', 'type':'text'},
            'dti':{'label':'DTI', 'type':'text'},
            'name':{'label':'Name', 'type':'text'},
            'symbol':{'label':'Symbol', 'type':'text'},
            'latitude':{'label':'Latitude', 'type':'text'},
            'longitude':{'label':'Longitude', 'type':'text'},
            'speed':{'label':'Longitude', 'type':'text'},
            'course':{'label':'Course', 'type':'text'},
            'altitude':{'label':'Altitude', 'type':'text'},
            'frequency':{'label':'Frequency', 'type':'text'},
            'offset':{'label':'Offset', 'type':'text'},
            'tone':{'label':'Tone', 'type':'text'},
            'system':{'label':'System', 'type':'text'},
            'status':{'label':'Status', 'type':'text'},
            'telemetry':{'label':'Telemetry', 'type':'text'},
            }},
        '_comment':{'label':'Comment', 'fields':{
            'comment':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_aprs_main.edit.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_aprs_main.edit.entry_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_aprs_main.edit.remove();'},
            }},
        };
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.aprs.entryHistory', 'args':{'tnid':M.curTenantID, 'entry_id':this.entry_id, 'field':i}};
    }
    this.edit.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.aprs.entryGet', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_aprs_main.edit;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_aprs_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.entry_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.aprs.entryUpdate', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('qruqsp.aprs.entryAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_aprs_main.edit.entry_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.edit.remove = function() {
        if( confirm('Are you sure you want to remove entry?') ) {
            M.api.getJSONCb('qruqsp.aprs.entryDelete', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_aprs_main.edit.close();
            });
        }
    }
    this.edit.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_aprs_main.edit.save(\'M.qruqsp_aprs_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) + 1] + ');\');';
        }
        return null;
    }
    this.edit.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) > 0 ) {
            return 'M.qruqsp_aprs_main.edit.save(\'M.qruqsp_aprs_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) - 1] + ');\');';
        }
        return null;
    }
    this.edit.addButton('save', 'Save', 'M.qruqsp_aprs_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

