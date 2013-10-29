/**
 * Copyright (c) 2008 Kelvin Luck (http://www.kelvinluck.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * .
 * $Id: jquery.datePicker.js 103 2010-09-22 08:54:28Z kelvin.luck $
 **/

(function($){
    
    $.fn.extend({
/**
 * Render a calendar table into any matched elements.
 * 
 * @param Object s (optional) Customize your calendars.
 * @option Number month The month to render (NOTE that months are zero based). Default is today's month.
 * @option Number year The year to render. Default is today's year.
 * @option Function renderCallback A reference to a function that is called as each cell is rendered and which can add classes and event listeners to the created nodes. Default is no callback.
 * @option Number showHeader Whether or not to show the header row, possible values are: $.dpConst.SHOW_HEADER_NONE (no header), $.dpConst.SHOW_HEADER_SHORT (first letter of each day) and $.dpConst.SHOW_HEADER_LONG (full name of each day). Default is $.dpConst.SHOW_HEADER_SHORT.
 * @option String hoverClass The class to attach to each cell when you hover over it (to allow you to use hover effects in IE6 which doesn't support the :hover pseudo-class on elements other than links). Default is dp-hover. Pass false if you don't want a hover class.
 * @type jQuery
 * @name renderCalendar
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('#calendar-me').renderCalendar({month:0, year:2007});
 * @desc Renders a calendar displaying January 2007 into the element with an id of calendar-me.
 *
 * @example
 * var testCallback = function($td, thisDate, month, year)
 * {
 * if ($td.is('.current-month') && thisDate.getDay() == 4) {
 *      var d = thisDate.getDate();
 *      $td.bind(
 *          'click',
 *          function()
 *          {
 *              alert('You clicked on ' + d + '/' + (Number(month)+1) + '/' + year);
 *          }
 *      ).addClass('thursday');
 *  } else if (thisDate.getDay() == 5) {
 *      $td.html('Friday the ' + $td.html() + 'th');
 *  }
 * }
 * $('#calendar-me').renderCalendar({month:0, year:2007, renderCallback:testCallback});
 * 
 * @desc Renders a calendar displaying January 2007 into the element with an id of calendar-me. Every Thursday in the current month has a class of "thursday" applied to it, is clickable and shows an alert when clicked. Every Friday on the calendar has the number inside replaced with text.
 **/
        renderCalendar  :   function(s)
        {
            var dc = function(a)
            {
                return document.createElement(a);
            };

            s = $.extend({}, $.fn.datePicker.defaults, s);
            
            if (s.showHeader != $.dpConst.SHOW_HEADER_NONE) {
                var headRow = $(dc('tr'));
                for (var i=Date.firstDayOfWeek; i<Date.firstDayOfWeek+7; i++) {
                    var weekday = i%7;
                    var day = Date.dayNames[weekday];
                    headRow.append(
                        jQuery(dc('th')).attr({'scope':'col', 'abbr':day, 'title':day, 'class':(weekday == 0 || weekday == 6 ? 'weekend' : 'weekday')}).html(s.showHeader == $.dpConst.SHOW_HEADER_SHORT ? day.substr(0, 1) : day)
                    );
                }
            };
            
            var calendarTable = $(dc('table'))
                                    .attr(
                                        {
                                            'cellspacing':2
                                        }
                                    )
                                    .addClass('jCalendar')
                                    .append(
                                        (s.showHeader != $.dpConst.SHOW_HEADER_NONE ? 
                                            $(dc('thead'))
                                                .append(headRow)
                                            :
                                            dc('thead')
                                        )
                                    );
            var tbody = $(dc('tbody'));
            
            var today = (new Date()).zeroTime();
            today.setHours(12);
            
            var month = s.month == undefined ? today.getMonth() : s.month;
            var year = s.year || today.getFullYear();
            
            var currentDate = (new Date(year, month, 1, 12, 0, 0));
            
            
            var firstDayOffset = Date.firstDayOfWeek - currentDate.getDay() + 1;
            if (firstDayOffset > 1) firstDayOffset -= 7;
            var weeksToDraw = Math.ceil(( (-1*firstDayOffset+1) + currentDate.getDaysInMonth() ) /7);
            currentDate.addDays(firstDayOffset-1);
            
            var doHover = function(firstDayInBounds)
            {
                return function()
                {
                    if (s.hoverClass) {
                        var $this = $(this);
                        if (!s.selectWeek) {
                            $this.addClass(s.hoverClass);
                        } else if (firstDayInBounds && !$this.is('.disabled')) {
                            $this.parent().addClass('activeWeekHover');
                        }
                    }
                }
            };
            var unHover = function()
            {
                if (s.hoverClass) {
                    var $this = $(this);
                    $this.removeClass(s.hoverClass);
                    $this.parent().removeClass('activeWeekHover');
                }
            };

            var w = 0;
            while (w++<weeksToDraw) {
                var r = jQuery(dc('tr'));
                var firstDayInBounds = s.dpController ? currentDate > s.dpController.startDate : false;
                for (var i=0; i<7; i++) {
                    var thisMonth = currentDate.getMonth() == month;
                    var d = $(dc('td'))
                                .text(currentDate.getDate() + '')
                                .addClass((thisMonth ? 'current-month ' : 'other-month ') +
                                                    (currentDate.isWeekend() ? 'weekend ' : 'weekday ') +
                                                    (thisMonth && currentDate.getTime() == today.getTime() ? 'today ' : '')
                                )
                                .data('datePickerDate', currentDate.asString())
                                .hover(doHover(firstDayInBounds), unHover)
                            ;
                    r.append(d);
                    if (s.renderCallback) {
                        s.renderCallback(d, currentDate, month, year);
                    }
                    // addDays(1) fails in some locales due to daylight savings. See issue 39.
                    //currentDate.addDays(1);
                    // set the time to midday to avoid any weird timezone issues??
                    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate()+1, 12, 0, 0);
                }
                tbody.append(r);
            }
            calendarTable.append(tbody);
            
            return this.each(
                function()
                {
                    $(this).empty().append(calendarTable);
                }
            );
        },
/**
 * Create a datePicker associated with each of the matched elements.
 *
 * The matched element will receive a few custom events with the following signatures:
 *
 * dateSelected(event, date, $td, status)
 * Triggered when a date is selected. event is a reference to the event, date is the Date selected, $td is a jquery object wrapped around the TD that was clicked on and status is whether the date was selected (true) or deselected (false)
 * 
 * dpClosed(event, selected)
 * Triggered when the date picker is closed. event is a reference to the event and selected is an Array containing Date objects.
 *
 * dpMonthChanged(event, displayedMonth, displayedYear)
 * Triggered when the month of the popped up calendar is changed. event is a reference to the event, displayedMonth is the number of the month now displayed (zero based) and displayedYear is the year of the month.
 *
 * dpDisplayed(event, $datePickerDiv)
 * Triggered when the date picker is created. $datePickerDiv is the div containing the date picker. Use this event to add custom content/ listeners to the popped up date picker.
 *
 * @param Object s (optional) Customize your date pickers.
 * @option Number month The month to render when the date picker is opened (NOTE that months are zero based). Default is today's month.
 * @option Number year The year to render when the date picker is opened. Default is today's year.
 * @option String|Date startDate The first date date can be selected.
 * @option String|Date endDate The last date that can be selected.
 * @option Boolean inline Whether to create the datePicker as inline (e.g. always on the page) or as a model popup. Default is false (== modal popup)
 * @option Boolean createButton Whether to create a .dp-choose-date anchor directly after the matched element which when clicked will trigger the showing of the date picker. Default is true.
 * @option Boolean showYearNavigation Whether to display buttons which allow the user to navigate through the months a year at a time. Default is true.
 * @option Boolean closeOnSelect Whether to close the date picker when a date is selected. Default is true.
 * @option Boolean displayClose Whether to create a "Close" button within the date picker popup. Default is false.
 * @option Boolean selectMultiple Whether a user should be able to select multiple dates with this date picker. Default is false.
 * @option Number numSelectable The maximum number of dates that can be selected where selectMultiple is true. Default is a very high number.
 * @option Boolean clickInput If the matched element is an input type="text" and this option is true then clicking on the input will cause the date picker to appear.
 * @option Boolean rememberViewedMonth Whether the datePicker should remember the last viewed month and open on it. If false then the date picker will always open with the month for the first selected date visible.
 * @option Boolean selectWeek Whether to select a complete week at a time...
 * @option Number verticalPosition The vertical alignment of the popped up date picker to the matched element. One of $.dpConst.POS_TOP and $.dpConst.POS_BOTTOM. Default is $.dpConst.POS_TOP.
 * @option Number horizontalPosition The horizontal alignment of the popped up date picker to the matched element. One of $.dpConst.POS_LEFT and $.dpConst.POS_RIGHT.
 * @option Number verticalOffset The number of pixels offset from the defined verticalPosition of this date picker that it should pop up in. Default in 0.
 * @option Number horizontalOffset The number of pixels offset from the defined horizontalPosition of this date picker that it should pop up in. Default in 0.
 * @option (Function|Array) renderCallback A reference to a function (or an array of separate functions) that is called as each cell is rendered and which can add classes and event listeners to the created nodes. Each callback function will receive four arguments; a jquery object wrapping the created TD, a Date object containing the date this TD represents, a number giving the currently rendered month and a number giving the currently rendered year. Default is no callback.
 * @option String hoverClass The class to attach to each cell when you hover over it (to allow you to use hover effects in IE6 which doesn't support the :hover pseudo-class on elements other than links). Default is dp-hover. Pass false if you don't want a hover class.
 * @option String autoFocusNextInput Whether focus should be passed onto the next input in the form (true) or remain on this input (false) when a date is selected and the calendar closes
 * @type jQuery
 * @name datePicker
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('input.date-picker').datePicker();
 * @desc Creates a date picker button next to all matched input elements. When the buttoearSelected();
                                    } else {
                                        var d = Date.fromString(this.value);
                                        if (d) {
                                            controller.setSelected(d, true, true);
                                        }
                                    }
                                }
                            );
                        if (s.clickInput) {
                            $this.bind(
                                'click',
                                function()
                                {
                                    // The change event doesn't happen until the input loses focus so we need to manually trigger it...
                                    $this.trigger('change');
                                    $this.dpDisplay();
                                }
                            );
                        }
                        var d = Date.fromString(this.value);
                        if (this.value != '' && d) {
                            controller.setSelected(d, true, true);
                        }
                    }
                    
                    $this.addClass('dp-applied');
                    
                }
            )
        },
/**
 * Disables or enables this date picker
 *
 * @param Boolean s Whether to disable (true) or enable (false) this datePicker
 * @type jQuery
 * @name dpSetDisabled
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * $('.date-picker').dpSetDisabled(true);
 * @desc Prevents this date picker from displaying and adds a class of dp-disabled to it (and it's associated button if it has one) for styling purposes. If the matched element is an input field then it will also set the disabled attribute to stop people directly editing the field.
 **/
        dpSetDisabled : function(s)
        {
            return _w.call(this, 'setDisabled', s);
        },
/**
 * Updates the first selectable date for any date pickers on any matched elements.
 *
 * @param String|Date d A Date object or string representing the first selectable date (formatted according to Date.format).
 * @type jQuery
 * @name dpSetStartDate
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * $('.date-picker').dpSetStartDate('01/01/2000');
 * @desc Creates a date picker associated with all elements with a class of "date-picker" then sets the first selectable date for each of these to the first day of the millenium.
 **/
        dpSetStartDate : function(d)
        {
            return _w.call(this, 'setStartDate', d);
        },
/**
 * Updates the last selectable date for any date pickers on any matched elements.
 *
 * @param String|Date d A Date object or string representing the last selectable date (formatted according to Date.format).
 * @type jQuery
 * @name dpSetEndDate
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * $('.date-picker').dpSetEndDate('01/01/2010');
 * @desc Creates a date picker associated with all elements with a class of "date-picker" then sets the last selectable date for each of these to the first Janurary 2010.
 **/
        dpSetEndDate : function(d)
        {
            return _w.call(this, 'setEndDate', d);
        },
/**
 * Gets a list of Dates currently selected by this datePicker. This will be an empty array if no dates are currently selected or NULL if there is no datePicker associated with the matched element.
 *
 * @type Array
 * @name dpGetSelected
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * alert($('.date-picker').dpGetSelected());
 * @desc Will alert an empty array (as nothing is selected yet)
 **/
        dpGetSelected : function()
        {
            var c = _getController(this[0]);
            if (c) {
                return c.getSelected();
            }
            return null;
        },
/**
 * Selects or deselects a date on any matched element's date pickers. Deselcting is only useful on date pickers where selectMultiple==true. Selecting will only work if the passed date is within the startDate and endDate boundries for a given date picker.
 *
 * @param String|Date d A Date object or string representing the date you want to select (formatted according to Date.format).
 * @param Boolean v Whether you want to select (true) or deselect (false) this date. Optional - default = true.
 * @param Boolean m Whether you want the date picker to open up on the month of this date when it is next opened. Optional - default = true.
 * vents related to this change of selection. Optional - default = true.
 * @type jQuery
 * @name dpSetSelected
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * $('.date-picker').dpSetSelected('01/01/2010');
 * @desc Creates a date picker associated with all elements with a class of "date-picker" then sets the selected date on these date pickers to the first Janurary 2010. When the date picker is next opened it will display Janurary 2010.
 **/
        dpSetSelected : function(d, v, m, e)
        {
            if (v == undefined) v=true;
            if (m == undefined) m=true;
            if (e == undefined) e=true;
            return _w.call(this, 'setSelected', Date.fromString(d), v, m, e);
        },
/**
 * Sets the month that will be displayed when the date picker is next opened. If the passed month is before startDate then the month containing startDate will be displayed instead. If the passed month is after endDate then the month containing the endDate will be displayed instead.
 *
 * @param Number m The month you want the date picker to display. Optional - defaults to the currently displayed month.
 * @param Number y The year you want the date picker to display. Optional - defaults to the currently displayed year.
 * @type jQuery
 * @name dpSetDisplayedMonth
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-picker').datePicker();
 * $('.date-picker').dpSetDisplayedMonth(10, 2008);
 * @desc Creates a date picker associated with all elements with a class of "date-picker" then sets the selected date on these date pickers to the first Janurary 2010. When the date picker is next opened it will display Janurary 2010.
 **/
        dpSetDisplayedMonth : function(m, y)
        {
            return _w.call(this, 'setDisplayedMonth', Number(m), Number(y), true);
        },
/**
 * Displays the date picker associated with the matched elements. Since only one date picker can be displayed at once then the date picker associated with the last matched element will be the one that is displayed.
 *
 * @param HTMLElement e An element that you want the date picker to pop up relative in position to. Optional - default behaviour is to pop up next to the element associated with this date picker.
 * @type jQuery
 * @name dpDisplay
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.co.kelvinluck.com/)
 *
 * @example $('#date-picker').datePicker();
 * $('#date-picker').dpSetOffset(-20, 200);
 * @desc Creates a date picker associated with the element with an id of date-picker and makes it so that when this date picker pops up it will be 20 pixels above and 200 pixels to the right of it's default position.
 **/
        dpSetOffset : function(v, h)
        {
            return _w.call(this, 'setOffset', v, h);
        },
/**
 * Closes the open date picker associated with this element.
 *
 * @type jQuery
 * @name dpClose
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 * @example $('.date-pick')
 *      .datePicker()
 *      .bind(
 *          'focus',
 *          function()
 *          {
 *              $(this).dpDisplay();
 *          }
 *      ).bind(
 *          'blur',
 *          function()
 *          {
 *              $(this).dpClose();
 *          }
 *      );
 **/
        dpClose : function()
        {
            return _w.call(this, '_closeCalendar', false, this[0]);
        },
/**
 * Rerenders the date picker's current month (for use with inline calendars and renderCallbacks).
 *
 * @type jQuery
 * @name dpRerenderCalendar
 * @cat plugins/datePicker
 * @author Kelvin Luck (http://www.kelvinluck.com/)
 *
 **/
        dpRerenderCalendar : function()
        {
            return _w.call(this, '_rerenderCalendar');
        },
        // private function called on unload to clean up any expandos etc and prevent memory links...
        _dpDestroy : function()
        {
            // TODO - implement this?
        }
    });
    
    // private internal function to cut down on the amount of code needed where we forward
    // dp* methods on the jQuery object on to the relevant DatePicker controllers...
    var _w = function(f, a1, a2, a3, a4)
    {
        return this.each(
            function()
            {
                var c = _getController(this);
                if (c) {
                    c[f](a1, a2, a3, a4);
                }
            }
        );
    };
    
    function DatePicker(ele)
    {
        this.ele = ele;
        
        // initial values...
        this.displayedMonth     =   null;
        this.displayedYear      =   null;
        this.startDate          =   null;
        this.endDate            =   null;
        this.showYearNavigation =   null;
        this.closeOnSelect      =   null;
        this.displayClose       =   null;
        this.rememberViewedMonth=   null;
        this.selectMultiple     =   nullontalPosition = s.horizontalPosition;
                this.hoverClass = s.hoverClass;
                this.setOffset(s.verticalOffset, s.horizontalOffset);
                this.inline = s.inline;
                this.settings = s;
                if (this.inline) {
                    this.context = this.ele;
                    this.display();
                }
            },
            setStartDate : function(d)
            {
                if (d) {
                    if (d instanceof Date) {
                        this.startDate = d;
                    } else {
                        this.startDate = Date.fromString(d);
                    }
                }
                if (!this.startDate) {
                    this.startDate = (new Date()).zeroTime();
                }
                this.setDisplayedMonth(this.displayedMonth, this.displayedYear);
            },
            setEndDate : function(d)
            {
                if (d) {
                    if (d instanceof Date) {
                        this.endDate = d;
                    } else {
                        this.endDate = Date.fromString(d);
                    }
                }
                if (!this.endDate) {
                    this.endDate = (new Date('12/31/2999')); // using the JS Date.parse function which expects mm/dd/yyyy
                }
                if (this.endDate.getTime() < this.startDate.getTime()) {
                    this.endDate = this.startDate;
                }
                this.setDisplayedMonth(this.displayedMonth, this.displayedYear);
            },
            setPosition : function(v, h)
            {
                this.verticalPosition = v;
                this.horizontalPosition = h;
            },
            setOffset : function(v, h)
            {
                this.verticalOffset = parseInt(v) || 0;
                this.horizontalOffset = parseInt(h) || 0;
            },
            setDisabled : function(s)
            {
                $e = $(this.ele);
                $e[s ? 'addClass' : 'removeClass']('dp-disabled');
                if (this.button) {
                    $but = $(this.button);
                    $but[s ? 'addClass' : 'removeClass']('dp-disabled');
                    $but.attr('title', s ? '' : $.dpText.TEXT_CHOOSE_DATE);
                }
                if ($e.is(':text')) {
                    $e.attr('disabled', s ? 'disabled' : '');
                }
            },
            setDisplayedMonth : function(m, y, rerender)
            {
                if (this.startDate == undefined || this.endDate == undefined) {
                    return;
                }
                var s = new Date(this.startDate.getTime());
                s.setDate(1);
                var e = new Date(this.endDate.getTime());
                e.setDate(1);
                
                var t;
                if ((!m && !y) || (isNaN(m) && isNaN(y))) {
                    // no month or year passed - default to current month
                    t = new Date().zeroTime();
                    t.setDate(1);
                } else if (isNaN(m)) {
                    // just year passed in - presume we want the displayedMonth
                    t = new Date(y, this.displayedMonth, 1);
                } else if (isNaN(y)) {
                ),
                                        $('<a class="dp-nav-next-month" href="#" title="' + $.dpText.TEXT_NEXT_MONTH + '">&gt;</a>')
                                            .bind(
                                                'click',
                                                function()
                                                {
                                                    return c._displayNewMonth.call(c, this, 1, 0);
                                                }
                                            )
                                    ),
                                $('<div class="dp-calendar"></div>')
                            )
                            .bgIframe()
                        );
                    
                var $pop = this.inline ? $('.dp-popup', this.context) : $('#dp-popup');
                
                if (this.showYearNavigation == false) {
                    $('.dp-nav-prev-year, .dp-nav-next-year', c.context).css('display', 'none');
                }
                if (this.displayClose) {
                    $pop.append(
                        $('<a href="#" id="dp-close">' + $.dpText.TEXT_CLOSE + '</a>')
                            .bind(
                                'click',
                                function()
                                {
                                    c._closeCalendar();
                                    return false;
                                }
                            )
                    );
                }
                c._renderCalendar();

                $(this.ele).trigger('dpDisplayed', $pop);
                
                if (!c.inline) {
                    if (this.verticalPosition == $.dpConst.POS_BOTTOM) {
                        $pop.css('top', eleOffset.top + $ele.height() - $pop.height() + c.verticalOffset);
                    }
                    if (this.horizontalPosition == $.dpConst.POS_RIGHT) {
                        $pop.css('left', eleOffset.left + $ele.width() - $pop.width() + c.horizontalOffset);
                    }
//                  $('.selectee', this.context).focus();
                    $(document).bind('mousedown.datepicker', this._checkMouse);
                }
                
            },
            setRenderCallback : function(a)
            {
                if (a == null) return;
                if (a && typeof(a) == 'function') {
                    a = [a];
                }
                this.renderCallback = this.renderCallback.concat(a);
            },
            cellRender : function ($td, thisDate, month, year) {
                var c = this.dpController;
                var d = new Date(thisDate.getTime());
                
                // add our click handlers to deal with it when the days are clicked...
                
                $td.bind(
                    'click',
                    function()
                    {
                        var $this = $(this);
                        if (!$this.is('.disabled')) {
                            c.setSelected(d, !$this.is('.selected') || !c.selectMultiple, false, true);
                            if (c.closeOnSelect) {
                                // Focus the next input in the formâ€¦
                                if (c.settings.autoFocusNextInput) {
                                    var ele = c.ele;
                                    var found = false;
                                    $(':input', ele.form).each(
                                        function()
                                        {
                                            if (found) {
                                                $(this).focus();
                                                return false;
                                            }
                                            if (this == ele) {
                                                found = true;
                                            }
                                        }
                                    );
                                } else {
                                    c.ele.focus();
                                }
                                c._closeCalendar();
                            }
                        }
                    }
                );
                if (c.isSelected(d)) {
                    $td.addClass('selected');
                    if (c.settings.selectWeek)
                    {
                        $td.parent().addClass('selectedWeek');
                    }
                } else  if (c.selectMultiple && c.numSelected == c.numSelectable) {
                    $td.addClass('unselectable');
                }
                
            },
            _applyRenderCallbacks : function()
            {
                var c = this;
                $('td', this.context).each(
                    function()
                    {
                        for (var i=0; i<c.renderCallback.length; i++) {
                            $td = $(this);
                            c.renderCallback[i].apply(this, [$td, Date.fromString($td.data('datePickerDate')), c.displayedMonth, c.displayedYear]);
                        }
                    }
                );
                return;
            },
            // ele is the clicked button - only proceed if it doesn't have the class disabled...
            // m and y are -1, 0 or 1 depending which direction we want to go in...
            _displayNewMonth : function(ele, m, y) 
            {
                if (!$(ele).is('.disabled')) {
                    this.setDisplayedMonth(this.displayedMonth + m, this.displayedYear + y, true);
                }
                ele.blur();
                return false;
            },
            _rerenderCalendar : function()
            {
                this._clearCalendar();
                this._renderCalendar();
            },
            _renderCalendar : function()
            {
                // set the title...
                $('h2', this.context).html((new Date(this.displayedYear, this.displayedMonth, 1)).asString($.dpText.HEADER_FORMAT));
layedMonth == ed.getMonth()) {
                            $('.dp-calendar td.other-month', this.context).each(
                                function()
                                {
                                    var $this = $(this);
                                    var cellDay = Number($this.text());
                                    if (cellDay < 13 && cellDay > d) {
                                        $this.addClass('disabled');
                                    }
                                }
                            );
                        }
                    }
                }
                this._applyRenderCallbacks();
            },
            _closeCalendar : function(programatic, ele)
            {
                if (!ele || ele == this.ele)
                {
                    $(document).unbind('mousedown.datepicker');
                    $(document).unbind('keydown.datepicker');
                    this._clearCalendar();
                    $('#dp-popup a').unbind();
                    $('#dp-popup').empty().remove();
                    if (!programatic) {
                        $(this.ele).trigger('dpClosed', [this.getSelected()]);
                    }
                }
            },
            // empties the current dp-calendar div and makes sure that all events are unbound
            // and expandos removed to avoid memory leaks...
            _clearCalendar : function()
            {
                // TODO.
                $('.dp-calendar td', this.context).unbind();
                $('.dp-calendar', this.context).empty();
            }
        }
    );
    
    // static constants
    $.dpConst = {
        SHOW_HEADER_NONE    :   0,
        SHOW_HEADER_SHORT   :   1,
        SHOW_HEADER_LONG    :   2,
        POS_TOP             :   0,
        POS_BOTTOM          :   1,
        POS_LEFT            :   0,
        POS_RIGHT           :   1,
        DP_INTERNAL_FOCUS   :   'dpInternalFocusTrigger'
    };
    // localisable text
    $.dpText = {
        TEXT_PREV_YEAR      :   'Previous year',
        TEXT_PREV_MONTH     :   'Previous month',
        TEXT_NEXT_YEAR      :   'Next year',
        TEXT_NEXT_MONTH     :   'Next month',
        TEXT_CLOSE          :   'Close',
        TEXT_CHOOSE_DATE    :   'Choose date',
        HEADER_FORMAT       :   'mmmm yyyy'
    };
    // version
    $.dpVersion = '$Id: jquery.datePicker.js 103 2010-09-22 08:54:28Z kelvin.luck $';

    $.fn.datePicker.defaults = {
        month               : undefined,
        year                : undefined,
        showHeader          : $.dpConst.SHOW_HEADER_SHORT,
        startDate           : undefined,
        endDate             : undefined,
        inline              : false,
        renderCallback      : null,
        createButton        : true,
        showYearNavigation  : true,
        closeOnSelect       : true,
        displayClose        : false,
        selectMultiple      : false,
        numSelectable       : Number.MAX_VALUE,
        clickInput          : false,
        rememberViewedMonth : true,
        selectWeek          : false,
        verticalPosition    : $.dpConst.POS_TOP,
        horizontalPosition  : $.dpConst.POS_LEFT,
        verticalOffset      : 0,
        horizontalOffset    : 0,
        hoverClass          : 'dp-hover',
        autoFocusNextInput  : false
    };

    function _getController(ele)
    {
        if (ele._dpId) return $.event._dpCache[ele._dpId];
        return false;
    };
    
    // make it so that no error is thrown if bgIframe plugin isn't included (allows you to use conditional
    // comments to only include bgIframe where it is needed in IE without breaking this plugin).
    if ($.fn.bgIframe == undefined) {
        $.fn.bgIframe = function() {return this; };
    };


    // clean-up
    $(window)
        .bind('unload', function() {
            var els = $.event._dpCache || [];
            for (var i in els) {
                $(els[i].ele)._dpDestroy();
            }
        });
        
    
})(jQuery);


