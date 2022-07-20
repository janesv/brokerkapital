var obj = {};
var fields = {
    'insurance_company': 'страховая компания',
    'policy_cost': 'стоимость полиса',
    'policy_number': 'номер полиса',
    'comission_total': 'общая комиссия %',
    'comission_total_price': 'общая комиссия ₽',
    'comission_agent': 'комиссия агента %',
    'comission_agent_price': 'комиссия агента ₽',
    'comission_company': 'комиссия капитал %',
    'company_price': 'сумма капитал ₽',
    'discount_percent': 'скидка %',
    'discount_price': 'доплата из кассы капитал',
    'received_payment': 'получили кв от ск',
    'received_partial_payment': 'получена частичная кв от ск'
};
$('.group_custom-service_wd').each(function() {
    var id = $(this).data('group_id').replace(/\D+/g, "");
    obj[id] = {};
    $(this).find('.linked-form__field').each(function() {
        var title = $(this).find('.linked-form__field__label span').text().trim().toLowerCase();
        var field_id = $(this).find('[name*="CFV"]').attr('name').replace(/\D+/g, "");
        for (var key in fields) {
            if (title == fields[key]) {
                obj[id][key] = field_id;
                break;
            }
        }
    });
});
console.log(JSON.stringify(obj));