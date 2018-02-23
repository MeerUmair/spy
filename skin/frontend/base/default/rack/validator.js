Validation.addAllThese([
    ['validate-katakana', 'Please use full width Katakana only in this field.', function(v,elm) {
        return Validation.get('IsEmpty').test(v) || /^([\u30A1-\u30FC])*$/.test(v)
    }],

    ['validate-hiragana', 'Please use Hiragana only in this field.', function(v,elm) {
        return Validation.get('IsEmpty').test(v) || /^([\u3040-\u309F|\u30FB-\u30FC])*$/.test(v)
    }],

    ['validate-post', 'The length of Zip/Postalcode is %d digits and only numbers are allowed.', function(v,elm) {
        return Validation.get('IsEmpty').test(v) || /^\d{7}$/.test(v)
    }],

    ['validate-post', 'The length of Zip/Postalcode is %d digits and only numbers are allowed.', function(v,elm) {
        return Validation.get('IsEmpty').test(v) || /^\d{7}$/.test(v)
    }],

    ['validate-post2', 'The length of Zip/Postalcode is %d digits and only numbers and %s are allowed.', function(v,elm) {
        return Validation.get('IsEmpty').test(v) || /^\d{3}\-\d{4}$/.test(v)
    }]
]);