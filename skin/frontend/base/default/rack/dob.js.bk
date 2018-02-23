Varien.DOB.prototype = Object.extend(Varien.DOB.prototype, {
    initialize: function(selector, required, format) {
        var el = $$(selector)[0];
        var container       = {};
        container.day       = Element.select(el, '.dob-day select')[0];
        container.month     = Element.select(el, '.dob-month select')[0];
        container.year      = Element.select(el, '.dob-year select')[0];
        container.full      = Element.select(el, '.dob-full input')[0];
        container.advice    = Element.select(el, '.validation-advice')[0];

        new Varien.DateElement('container', container, required, format);
    }
});