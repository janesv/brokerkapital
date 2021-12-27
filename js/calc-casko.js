
// Файл обработки для формы каско

jQuery(document).ready(function ($) {

    var opened_flag;
    var value;
    var target;
    target = '.wrap';
    opened_flag = false;

    //Подгрузим форму
    $.ajax({
        type: "GET",
        url: "/casco_form/forma_osago.html",
        cache: false
    })
            .done(function (html) {
                $("#osago").append(html);

                //================ После аякс загрузки основной формы ========================

                //Подгрузим из файла список регионов проживания
                $.ajax({
                    type: "GET",
                    url: "/casco_form/regions.html",
                    cache: false
                })
                        .done(function (html) {
                            $("#regions3").append(html);
                        });

                //--------------------------------------------------------------

                //Зададим маску для ввода телефона
                $("#phone").mask("+7 (999) 999-9999");


                // Чтоб в поля можно было вводить только цифры, блокирует ввод буков
                $('.num').bind("change keyup input click", function () {
                    if (this.value.match(/[^0-9]/g)) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    }
                });

                $("#god_ispolz").change(function () {

                    var n = 0;
                    var god = $(this).val();

                    if (god == 'Первый')
                        n = 0;
                    if (god == 'Второй')
                        n = 1;
                    if (god == 'Третий')
                        n = 2;
                    if (god == 'Четвертый')
                        n = 3;
                    if (god == 'Пятый')
                        n = 4;
                    if (god == 'Шестой')
                        n = 5;
                    if (god == 'Седьмой')
                        n = 6;
                    if (god == 'Восьмой')
                        n = 7;
                    if (god == 'Девятый')
                        n = 8;
                    if (god == 'Десятый')
                        n = 9;

                    var gi = $('.use_osago .right-wrap .god_item');

                    gi.hide();
                    gi.each(function () {
                        if ($(this).index() < n)
                            $(this).show();

                    });


                });


                // Обрабатываем нажатие кнопки отправить
                $('#osaga_form_submit').click(function (e) {
                    e.preventDefault();

                    var error;
                    error = false;
                    //Валидация обязательных полей формы
                    //Спрячим все сообщения об ошибке
                    $(".required").each(function () {
                        $(this).removeClass("error");
                        $(this).find('.error-box').hide();
                    });


                    //Если обязательное поле не 3аполнено
                    $(".required").each(function () {

                        if ($(this).is('.visi')) {

                            if ($(this).find("input").val() == '') {

                                $(this).addClass("error");
                                $(this).find('.error-box').show();
                                error = true;



                            }

                        }

                    });

                    // Если нет ошибок заполнения формы - отправка данных 
                    if (!error) {

                        $('#osaga_form_submit').attr({'disabled': 'true', 'value': 'ОТПРАВКА ...'});



                        $.post('/casco_form/send.php', $('#osaga_form').serialize(), function (result) {

                            if (result == 'sent') {
                                //Если отправка прошла успешно


                                $('#mail_success').fadeIn(500);
                                setTimeout(function () {  //Задаем интервал, через который снова можно будет отправлять запрос.
                                    $('#mail_success').fadeOut(500);
                                    $('#osaga_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 3000);

                            } else {
                                //Если в процессе отправки случилась ошибка
                                $('#mail_fail').fadeIn(500);
                                setTimeout(function () {
                                    $('#mail_fail').fadeOut(500);
                                    $('#osaga_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 5000);
                            }
                        });

                    } //error

                }); // кнопка отправить

            });


    //Подгрузим форму  --------------- casco ----------------
//    $.ajax({
//        type: "GET",
//        url: "/casco_form/forma_casco.html",
//        cache: false
//    })
//            .done(function (html) {
//                $("#casco").append(html);

    //================ После аякс загрузки основной формы ========================

    if ($('#casco_form').length) {

        //Подгрузим из файла список с тачками и моделями и разместим его в контейнер комбо-тачки
        $.ajax({
            type: "GET",
            url: "/casco_form/tachki.html",
            cache: false
        })
                .done(function (html) {
                    $("#tachki").append(html);
                });

        //Подгрузим из файла список регионов проживания
        $.ajax({
            type: "GET",
            url: "/casco_form/regions.html",
            cache: false
        })
                .done(function (html) {
                    $("#regions").append(html);
                });

        //Подгружаем список банков
        $.ajax({
            type: "GET",
            url: "/casco_form/banks.html",
            cache: false
        })
                .done(function (html) {
                    $("#bank").append(html);
                });

        //--------------------------------------------------------------

        //Зададим маску для ввода телефона
        $("#phone").mask("+7 (999) 999-9999");

        //Прикрутим слайд цены
        $("#price-slider").slider({
            //range: true,
            value: 1000000,
            min: 0,
            max: 2000000,
            slide: function (event, ui) {
                $("#price").val(ui.value);
            }
        });

        $("#price").val($("#price-slider").slider("value"));
        // Меняем положение слайдера в соответствии с введенными цифрами
        $('#price').bind("change keyup input click", function () {
            var value1 = $("#price").val();
            $("#price-slider").slider("value", value1);
        });


        //----- Обрабатываем  нажатие по чекбоксу "Авто купленнное в кредит"  -------
        // При нажатии появляется блок для выбора банка, при  отмене исчезает
        $("#if_credit").on("click", function () {
            if ($(this).is(":checked")) {
                $('#credit').show();
                $('#bank-wrap').addClass('visi');
            } else {
                $('#credit').hide();
                $('#bank-wrap').removeClass('visi');
            }
        })


        //------ Обрабатываем выбор чекбокса другой банк -------------
        // При нажатии обнуляется значение поля банк
        $("#drugoy_bank").on("click", function () {
            if ($(this).is(":checked")) {
                $('#credit input').val('');
                if ($('#bank-wrap').is('.error')) {
                    $('#bank-wrap').removeClass("error");
                    $('#bank-wrap').find('.error-box').hide();
                }
            } else {
                //	   			
            }
        })

        // -- Если произвели выбор банка из списка - чекбокс "другой банк" обнулить ---
        $("#bank-input").change(function () {
            $('#drugoy_bank').removeAttr('checked');

        });

        // Чтоб в поля можно было вводить только цифры, блокирует ввод буков
        $('.num').bind("change keyup input click", function () {
            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });

        //Обработаем выбор числа водителей
        $("#num_vodilas").change(function () {

            $(this).val(value);

            $("#v-v1").removeClass('visi');
            $("#s-v1").removeClass('visi');
            $("#v-v2").removeClass('visi');
            $("#s-v2").removeClass('visi');
            $("#v-v3").removeClass('visi');
            $("#s-v3").removeClass('visi');
            $("#v-v4").removeClass('visi');
            $("#s-v4").removeClass('visi');


            if ($(this).val() == '1 человек') {

                $(".right-wrap .vrow").hide();
                $(".right-wrap #v1").show();
                $("#v-v1").addClass('visi');
                $("#s-v1").addClass('visi');
            }

            if ($(this).val() == '2 человека') {
                $(".right-wrap .vrow").hide();
                $(".right-wrap #v1").show();
                $(".right-wrap #v2").show();
                $("#v-v1").addClass('visi');
                $("#s-v1").addClass('visi');
                $("#v-v2").addClass('visi');
                $("#s-v2").addClass('visi');

            }
            if ($(this).val() == '3 человека') {
                $(".right-wrap .vrow").hide();
                $(".right-wrap #v1").show();
                $(".right-wrap #v2").show();
                $(".right-wrap #v3").show();
                $("#v-v1").addClass('visi');
                $("#s-v1").addClass('visi');
                $("#v-v2").addClass('visi');
                $("#s-v2").addClass('visi');
                $("#v-v3").addClass('visi');
                $("#s-v3").addClass('visi');
            }
            if ($(this).val() == '4 человека') {

                $(".right-wrap .vrow").hide();
                $(".right-wrap #v1").show();
                $(".right-wrap #v2").show();
                $(".right-wrap #v3").show();
                $(".right-wrap #v4").show();
                $("#v-v1").addClass('visi');
                $("#s-v1").addClass('visi');
                $("#v-v2").addClass('visi');
                $("#s-v2").addClass('visi');
                $("#v-v3").addClass('visi');
                $("#s-v3").addClass('visi');
                $("#v-v4").addClass('visi');
                $("#s-v4").addClass('visi');
            }

            //Уберем ошибку
            $(".vodily .right-wrap .required").each(function () {

                if ($(this).is('.error')) {
                    $(this).removeClass("error");
                    $(this).find('.error-box').hide();
                }
            });
        });


        // Обрабатываем нажатие кнопки отправить
        $('#casco_form_submit').click(function (e) {

            e.preventDefault();

            var error;
            error = false;
            //Валидация обязательных полей формы
            //Спрячим все сообщения об ошибке
            $(".required").each(function () {
                $(this).removeClass("error");
                $(this).find('.error-box').hide();
            });


            //Если обязательное поле не 3аполнено
            $(".required").each(function () {

                if ($(this).is('.visi')) {

                    if ($(this).find("input").val() == '') {

                        $(this).addClass("error");
                        $(this).find('.error-box').show();
                        error = true;



                        if ($(this).is('#bank-wrap')) {
                            if ($('#drugoy_bank').is(":checked")) {
                                $(this).removeClass("error");
                                $(this).find('.error-box').hide();
                                error = false;
                            }
                        }

                    }

                }

            });

            // Если нет ошибок заполнения формы - отправка данных 
            if (!error) {

                $('#casco_form_submit').attr({'disabled': 'true', 'value': 'ОТПРАВКА ...'});

                $.post('/casco_form/send.php', $('#casco_form').serialize(), function (result) {

                    if (result !== 'failed') {


                        $('#mail_success').fadeIn(400).delay(600);



                        setTimeout(function () {  //Задаем интервал, через который снова можно будет отправлять запрос.
                            $('#mail_success').fadeOut(900);
                            $('#casco_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                            $(".required").each(function () {
                                //Очистим все поля после отправки формы
                                //$(this).find('input').val('');
                            });

                        }
                        , 5000); //увеличил время благодарности

                    } else {

                        $('#mail_fail').fadeIn(500);
                        setTimeout(function () {
                            $('#mail_fail').fadeOut(500);
                            $('#casco_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                            $(".required").each(function () {
                                //Очистим все поля после отправки формы
                                //$(this).find('input').val('');
                            });

                        }
                        , 5000);
                    }
                });

            } //error

        }); // кнопка отправить
        $('#casco_form .wpcf7-form').on( 'submit',function () {
            $form = $(this);
            var res, label, k,val,name;
            res = '';
            k = 0;
            $form.find('.temp').remove();
            jQuery('#casco').find('input[type=text], input[type=checkbox]:checked, input[type=radio]:checked').each(function () {
                name = $(this).attr("name");
                if ($(this).parent().is(':visible') && $(this).parents('form').length==0) {
                    label = $(this).parent().parent().find('label').html();
                    if (name.indexOf('pole[v_v') > -1) {
                        k++;
                        label = 'Возраст ' + k + '-го водителя';
                    }
                    if (name.indexOf('pole[s_v') > -1) {
                        label = 'Стаж ' + k + '-го водителя';
                    }
                    if (name.indexOf('pole[pol_v') > -1) {
                        label = 'Пол ' + k + '-го водителя';
                    }
                    if (name.indexOf('pole[bank') > -1) {
                        label = 'Банк';
                    }
                    val = ($(this).val()?$(this).val():'не задано');
                    res += ' ' + label + ': ' + val + "\n";
                    $form.find('textarea.form-content').after('<input type="text" class="temp" name="'+name+'" value="'+val+'">');
                }
            });
            $form.find('.form-content').val(res);
            return false;
        });
        document.addEventListener( 'wpcf7mailsent', function( event ) {
            if ( '713' == event.detail.contactFormId ) {
                yaCounter35210840.reachGoal('kasko');
            }
        }, false );
    }
    //);


//--------------- вторая форма  автокредит ------------

//Подгрузим форму
    $.ajax({
        type: "GET",
        url: "/casco_form/forma_avtocredit.html",
        cache: false
    })
            .done(function (html) {
                $("#avtocredit").append(html);

                //================ После аякс загрузки основной формы ========================
                //Подгрузим из файла список с тачками и моделями и разместим его в контейнер комбо-тачки
                $.ajax({
                    type: "GET",
                    url: "/casco_form/tachki.html",
                    cache: false
                })
                        .done(function (html) {
                            $("#tachki2").append(html);
                        });

                //Подгрузим из файла список регионов проживания
                $.ajax({
                    type: "GET",
                    url: "/casco_form/regions.html",
                    cache: false
                })
                        .done(function (html) {
                            $("#regions2").append(html);
                        });


                //--------------------------------------------------------------

                //Зададим маску для ввода телефона
                $("#phone").mask("+7 (999) 999-9999");

                //Прикрутим слайд цены
                $("#price-slider").slider({
                    //range: true,
                    value: 3000000,
                    min: 0,
                    max: 6000000,
                    slide: function (event, ui) {
                        $("#price").val(ui.value);
                    }
                });

                $("#price").val($("#price-slider").slider("value"));
                // Меняем положение слайдера в соответствии с введенными цифрами
                $('#price').bind("change keyup input click", function () {
                    var value1 = $("#price").val();
                    $("#price-slider").slider("value", value1);
                });

                // Чтоб в поля можно было вводить только цифры, блокирует ввод буков
                $('.num').bind("change keyup input click", function () {
                    if (this.value.match(/[^0-9]/g)) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    }
                });


                // Обрабатываем нажатие кнопки отправить
                $('#avtocredit_form_submit').click(function (e) {

                    e.preventDefault();

                    var error;
                    error = false;
                    //Валидация обязательных полей формы
                    //Спрячим все сообщения об ошибке
                    $(".required").each(function () {
                        $(this).removeClass("error");
                        $(this).find('.error-box').hide();
                    });


                    //Если обязательное поле не 3аполнено
                    $(".required").each(function () {

                        if ($(this).is('.visi')) {

                            if ($(this).find("input").val() == '') {

                                $(this).addClass("error");
                                $(this).find('.error-box').show();
                                error = true;

                            }
                        }
                    });

                    // Если нет ошибок заполнения формы - отправка данных 
                    if (!error) {

                        $('#avtocredit_form_submit').attr({'disabled': 'true', 'value': 'ОТПРАВКА ...'});

                        $.post('/casco_form/send.php', $('#avtocredit_form').serialize(), function (result) {

                            if (result == 'sent') {


                                $('#mail_success').fadeIn(500);
                                setTimeout(function () {  //Задаем интервал, через который снова можно будет отправлять запрос.
                                    $('#mail_success').fadeOut(500);
                                    $('#avtocredit_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 3000);

                            } else {
                                //Если в процессе отправки случилась ошибка
                                $('#mail_fail').fadeIn(500);
                                setTimeout(function () {
                                    $('#mail_fail').fadeOut(500);
                                    $('#avtocredit_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 5000);
                            }
                        });

                    } //error

                }); // кнопка отправить


            });




// -------------  Форма имущество --------------
    $.ajax({
        type: "GET",
        url: "/casco_form/forma_imushestvo.html",
        cache: false
    })
            .done(function (html) {
                $("#imushestvo").append(html);

                //================ После аякс загрузки основной формы ========================



                //Зададим маску для ввода телефона
                $("#phone").mask("+7 (999) 999-9999");


                // Обрабатываем нажатие кнопки отправить
                $('#imushestvo_form_submit').click(function (e) {
                    e.preventDefault();

                    var error;
                    error = false;
                    //Валидация обязательных полей формы
                    //Спрячим все сообщения об ошибке
                    $(".required").each(function () {
                        $(this).removeClass("error");
                        $(this).find('.error-box').hide();
                    });


                    //Если обязательное поле не 3аполнено
                    $(".required").each(function () {

                        if ($(this).is('.visi')) {

                            if ($(this).find("input").val() == '') {

                                $(this).addClass("error");
                                $(this).find('.error-box').show();
                                error = true;
                            }
                        }
                    });

                    // Если нет ошибок заполнения формы - отправка данных 
                    if (!error) {

                        $('#imushestvo_form_submit').attr({'disabled': 'true', 'value': 'ОТПРАВКА ...'});

                        $.post('/casco_form/send.php', $('#imushestvo_form').serialize(), function (result) {

                            if (result == 'sent') {
                                //Если отправка прошла успешно
                                $('#mail_success').fadeIn(500);
                                setTimeout(function () {  //Задаем интервал, через который снова можно будет отправлять запрос.
                                    $('#mail_success').fadeOut(500);
                                    $('#imushestvo_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 3000);

                            } else {
                                //Если в процессе отправки случилась ошибка
                                $('#mail_fail').fadeIn(500);
                                setTimeout(function () {
                                    $('#mail_fail').fadeOut(500);
                                    $('#imushestvo_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 5000);
                            }
                        });

                    } //error

                }); // кнопка отправить

            });


//----------------- Кредитная история -----------------

    $.ajax({
        type: "GET",
        url: "/casco_form/forma_creditstory.html",
        cache: false
    })
            .done(function (html) {
                $("#creditstory").append(html);

                //================ После аякс загрузки основной формы ========================



                //Зададим маску для ввода телефона
                $("#phone").mask("+7 (999) 999-9999");


                // Обрабатываем нажатие кнопки отправить
                $('#creditstory_form_submit').click(function (e) {
                    e.preventDefault();

                    var error;
                    error = false;
                    //Валидация обязательных полей формы
                    //Спрячим все сообщения об ошибке
                    $(".required").each(function () {
                        $(this).removeClass("error");
                        $(this).find('.error-box').hide();
                    });


                    //Если обязательное поле не 3аполнено
                    $(".required").each(function () {

                        if ($(this).is('.visi')) {

                            if ($(this).find("input").val() == '') {

                                $(this).addClass("error");
                                $(this).find('.error-box').show();
                                error = true;
                            }
                        }
                    });

                    // Если нет ошибок заполнения формы - отправка данных 
                    if (!error) {

                        $('#creditstory_form_submit').attr({'disabled': 'true', 'value': 'ОТПРАВКА ...'});

                        $.post('/casco_form/send.php', $('#creditstory_form').serialize(), function (result) {

                            if (result == 'sent') {
                                //Если отправка прошла успешно
                                $('#mail_success').fadeIn(500);
                                setTimeout(function () {  //Задаем интервал, через который снова можно будет отправлять запрос.
                                    $('#mail_success').fadeOut(500);
                                    $('#creditstory_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 3000);

                            } else {
                                //Если в процессе отправки случилась ошибка
                                $('#mail_fail').fadeIn(500);
                                setTimeout(function () {
                                    $('#mail_fail').fadeOut(500);
                                    $('#creditstory_form_submit').removeAttr('disabled').attr('value', 'Отправить');
                                    $(".required").each(function () {
                                        //Очистим все поля после отправки формы
                                        //$(this).find('input').val('');
                                    });

                                }
                                , 5000);
                            }
                        });

                    } //error

                }); // кнопка отправить

            });

// -------------- обработка инпута --------------------------------------------

    //Если клацнули по input  - открыть окно выбора
    $(document).on("click", ".wrap input", function (event) {
        event.preventDefault();
        $('.wrap .combo-box').hide();
        $('.wrap .but').css('background', 'url(/casco_form/img/tri_down.png) no-repeat center');
        target = $(this).parent('.wrap');
        target.find('.combo-box').show();
        $(this).parents('.wrap').find('.combo-box > div > div').show();
        $(this).parents('.wrap').find('.combo-box > div > .hid').hide();

        target.find('.but').css('background', 'url(/casco_form/img/tri_up.png) no-repeat center');


        opened_flag = true;
        value = '';

        if (target.is('.error')) {
            target.removeClass("error");
            target.find('.error-box').hide();
        }

    });

    //Клацнули по кнопочке
    $(document).on("click", ".wrap .but", function (event) {

        //если список был открыт
        if (opened_flag) {

            //target.removeClass( "error" );
            //target.find('.error-box').hide();	

            target.find('.combo-box').hide();
            target.find('.but').css('background', 'url(/casco_form/img/tri_down.png) no-repeat center');
            $(this).css('background', 'url(/casco_form/img/tri_down.png) no-repeat center');
            //target.find('input').val('');
            //value='';
            opened_flag = false;
            //target='';
        }
        //если список был закрыт
        else {
            opened_flag = true;

            //target.removeClass( "error" );
            //target.find('.error-box').hide();

            value = '';
            $('.wrap .combo-box').hide();
            target = $(this).parent('.wrap');
            target.children('.combo-box').show();
            $(this).css('background', 'url(/casco_form/img/tri_up.png) no-repeat center');

            $(this).parents('.wrap').find('.combo-box > div > div').show();
            $(this).parents('.wrap').find('.combo-box > div > .hid').hide();

            opened_flag = true;


        }
    });


    //Если клацнули вне окна
    $(document).click(function (event) {
        if ($(event.target).closest('.wrap').length)
            return;

        $(".wrap .combo-box").hide();
        $(".wrap .combo-box .hid").hide();

        $('.wrap .but').css('background', 'url(/casco_form/img/tri_down.png) no-repeat center');
        if (opened_flag) {
            opened_flag = false;
            target = '';
        }

        event.stopPropagation();
    });


    //Если клацнули по элементу сгенерированного списка 
    $(document).on("click", target + " .combo-box span", function (event) {
        event.preventDefault();

        target.removeClass("error");
        target.find('.error-box').hide();

        if ($(this).is('.has_childe')) {

            var id = $(this).attr('id');
            value = $(this).html() + '  ';
            target.find('input').val(value);
            target.find('input').trigger('change'); //чтоб можно было программно отследить изменение инпута - генерит событе change

            $(this).parents('.combo-box > div > div').hide();
            $(this).parents('.combo-box').find('div > div.' + id).show(); // Откроем список второго уровня вложенности	


        } else {

            value += $(this).html();

            $(target).find('input').val(value);
            target.find('input').trigger('change'); //чтоб можно было программно отследить изменение инпута - генерит событе change

            $(target).find('.combo-box').hide();
            $(target).find('.but').css('background', 'url(/casco_form/img/tri_down.png) no-repeat center');
            value = '';
            opened_flag = false;

            $('.hid').hide();

        }

    });

});

// Переход между формами
    jQuery(document).on('click', '.js-calc-form-button', calcButtonTapped);
    jQuery(document).on('click', '.js-order-form-button', orderButtonTapped);
    jQuery(document).on('click', '.js-back-button', backButtonTapped);
     
    function calcButtonTapped() {
        yaCounter35210840.reachGoal('kasko2');
        jQuery('.kasko-buttons.row').hide();
        jQuery('#kasko-examples-form').hide();
        jQuery('.js-back-button').show();
        jQuery('#kasko-calc-form').show();
    }
    
    function orderButtonTapped() {

        jQuery('.kasko-buttons.row').hide();
        jQuery('#kasko-examples-form').hide();
        jQuery('.js-back-button').show();
        jQuery('#kasko-order-form').show();
    }
    
    function backButtonTapped() {
       
        jQuery('#kasko-examples-form').show();
        jQuery('.kasko-buttons.row').show();
        
        jQuery('.js-back-button').hide();
        jQuery('#kasko-order-form').hide();
        jQuery('#kasko-calc-form').hide();
    }