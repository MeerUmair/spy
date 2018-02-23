/**
 * Rack_Getpostcode専用
 *
 * @author ndlinh (nduylinh@gmail.com)
 */

Getpostcode = Class.create();
Getpostcode.prototype = {
    initialize: function(url, searchBtn, notFoundMsg, prefectureField, cityField, streetField, postCodeField, postCodeField1, postCodeField2) {
        this.url                = url;
        
        this.postCodeField      = $(postCodeField);
        if(!this.postCodeField) {
            this.postCodeField2 = $(postCodeField2);
            this.postCodeField1 = $(postCodeField1);
        }
        
        this.prefectureField    = $(prefectureField);
        this.cityField          = $(cityField);
        this.streetField        = $(streetField);
        this.notFoundMessage    = notFoundMsg;
        
        if (searchBtn == false) {
            if(!this.postCodeField) {
                //Event.observe(this.postCodeField1, 'change', this.update.bind(this));
                Event.observe(this.postCodeField2, 'change', this.update.bind(this));
            } else {
                Event.observe(this.postCodeField, 'change', this.update.bind(this));
            }
        } else {
            this.searchBtn          = $(searchBtn);
            Event.observe(this.searchBtn, 'click', this.update.bind(this));
        }
    },
    
    update: function() {
        if (this.url == '') return;
        var $self = this;
        var value = '';
        
        if(!$self.postCodeField) {
            value = $self.postCodeField1.value + $self.postCodeField2.value;
        } else {
            value = $self.postCodeField.value;
        }
        
        new Ajax.Request(this.url, {
            method: 'get',
            parameters: {"postcode": value},
            onSuccess: function(response) {
                try {
                    var json = response.responseText.evalJSON();
                    if (json && json.prefecture_name != undefined) {
                        $self.setValues(json);
                    } else {
                        $self.showNotfoundError();
                    }
                } catch (e) { alert(e);}
            }
        });
    },

    setValues: function(data) {
        this.clearError();
        //set prefecture name
        if (this.prefectureField.tagName == 'SELECT') {
            for (var i = 0; i < this.prefectureField.options.length; i++) {
                if (this.prefectureField.options[i].text == data.prefecture_name) {
                    this.prefectureField.selectedIndex = i;
                    break;
                }
            }
        } else {
            this.prefectureField.value = data.prefecture_name;
        }
        
        //set 市区町村
        this.cityField.value = data.city_ward;
        
        //Focus street field for customer enter 建物および部屋番号
        this.streetField.value = data.area;
        this.streetField.focus();
    },

    clearValues: function() {
        this.cityField.value = '';
        if(!this.postCodeField) {
            this.postCodeField1.focus();
            this.postCodeField1.select();
        } else {
            this.postCodeField.focus();
            this.postCodeField.select();
        }
    },

    showNotfoundError: function() {
        this.clearError();
        if(!this.postCodeField) {
            this.postCodeField1.addClassName('validation-failed');
            this.postCodeField2.addClassName('validation-failed');
        } else {
            this.postCodeField.addClassName('validation-failed');
        }
        var html = '<div id="advice-required-entry-zip" class="validation-advice">' + this.notFoundMessage + '</div>';
        if (this.searchBtn !== undefined) {
            this.searchBtn.insert({after: html});
        } else {
            if(!this.postCodeField) {
                this.postCodeField2.insert({after: html});
                //this.postCodeField2.insert({after: html});
            } else {
                this.postCodeField.insert({after: html});
            }
        }
    },

    clearError: function() {
        if(!this.postCodeField) {
            this.postCodeField1.removeClassName('validation-failed');
            this.postCodeField2.removeClassName('validation-failed');
        } else {
            this.postCodeField.removeClassName('validation-failed');
        }
            
        if ($('advice-required-entry-zip')) {
            $('advice-required-entry-zip').remove();
        }
    },
    
    appendButton: function(buttonLabel, zipFieldSize) {
        //if (zipFieldSize != '' ) {
        //    this.postCodeField.setStyle({width: zipFieldSize});
        //}
        var autoId = 'getaddress_auto' + Math.floor(Math.random()*10000);
        var GetPostCodeBtn = '<button class="button getaddress" id="' + autoId + '" type="button"><span><span>' + buttonLabel + '</span></span></button>';
        if(!this.postCodeField) {
            this.postCodeField2.insert({after: GetPostCodeBtn});
        } else {
            this.postCodeField.insert({after: GetPostCodeBtn});
        }
        this.searchBtn = $(autoId);
        Event.observe(this.searchBtn, 'click', this.update.bind(this));
        if(!this.postCodeField) {
            Event.stopObserving(this.postCodeField2, 'change');
            //Event.stopObserving(this.postCodeField2, 'change');
        } else {
            Event.stopObserving(this.postCodeField, 'change');
        }
    }
}