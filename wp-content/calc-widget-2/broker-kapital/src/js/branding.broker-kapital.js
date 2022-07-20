(function (angular) {
  angular.module('brandingConfigs', [])
      .service('brandingConfigs', [
        '$stateParams',
        'moduleType',
        'CONSTS',
        function ($stateParams, moduleType, CONSTS) {
          var config = {
            kasko: {
              locales: {
                'mileage-title': {
                  'ru': 'Другой пробег',
                  'en': 'Other mileage'
                }
              },
              exploitation_area_all_are_hidden: true,
              watermark_is_hidden: true,
              with_sms: false,
              exploitation_areas: [30233],
              owner_registrations_expanded: [
                {
                  id: '30233_1',
                  title: 'Воронежская обл.'
                },
              ],
              preCalculationParse: function (calculation) {
                if (calculation.owner_registration) {
                  calculation.owner_registration = String(calculation.owner_registration).replace(/_\d/, '');
                }
              },
              calculationInterParamsDefaults: {},
              sendLetterBeforeOrder: true, /* Отпр. письмо после верификации */
              payment_form_is_hidden: true,
              arrival_time_is_hidden: true,
              steps_buttons_color: 'branded',
              main_buttons_color: 'branded',
              united_buttons_color: '',
              // ya_metrika_id: 41407164,
              link_to_registration_in_email: false,
              payment_forms: [
                {
                  id: 107,
                  title: 'У официального дилера'
                },
                {
                  id: 105,
                  title: 'Не официальные СТОА'
                }
              ],
              templates: {
                third_big_step: '/brands/broker-kapital/src/modules.shared/carFilter/views/third_big_step.html',
              }
            },
            osago: {
              // ya_metrika_id: 41407164,
              with_sms: false,
              owner_registration_all_are_hidden: true,
              owner_registrations: [29650],
              show_user_agreement: true,
              watermark_is_hidden: true,
              show_personal_data: true,
              sendLetterBeforeOrder: true, /* Отпр. письмо после верификации */
              steps_buttons_color: 'branded',
              main_buttons_color: 'branded',
              with_calculation_fake_data: false,
              fake_calculation_fields: [ {name: "car_model", value: 7559}, {name: "car_mark", value: 297}],
              link_to_registration_in_email: false,
              templates: {
                adding_documents: '/brands/broker-kapital/src/modules.osago/AddingDocuments/views/adding_documents.uploader.html'
              }
            },
            casco: {
              with_sms: true,
                without_steps_history: false,
                without_osago: true,
                with_payment_type: false,
                without_delivery: true,
                watermark_is_hidden: true,
                sendLetterBeforeOrder: true,
                disable_discount: false,
                steps_discount: {
                    casco_car_mark: 1,
                    casco_year: 1,
                    casco_car_model: 1,
                    sms_verification: 2,
                    casco_car_other: 1,
                    casco_insurance_params: 2,
                    drivers: 2
                },
                steps_discount_titles: {
                    sms_verification: "+2% за указание контактных данных"
                },
                with_alternative_fourth_step: true,
                with_agreement_on_ordering: false,
                with_bottom_programs: false,
                recalculation_module: "top",
                hidden_in_mail: [],
                templates: {
                    navigation: CONSTS.pathToModules + "casco/formationPolicyS/views/navigation.html",
                    third_big_step: "/brands/broker-kapital/src/modules.shared/carFilter/views/third_big_step.html",
                    step_3: "/brands/broker-kapital/src/modules.casco/calculatorS/views/calculation.step_3.html"
                }
            }
          };

          return config[moduleType];
        }
      ])
      .service('stepsService', [
        'moduleType',
        function (moduleType) {
          var params = {
            kasko: {
              stepsHacks: {},
              steps: {
                first: [
                  'year',
                  'car_mark',
                  'car_model_group',
                  'car_model',
                  'engine_power',
                  'exploitation_area',
                  'car_cost',
                  'mileage'
                ],
                second: [
                  'drivers_count',
                  function (calculation) {
                    return calculation.is_multidrive ? 'is_multidrive' : 'drivers';
                  },
                  'calc'
                ]
              }
            },
            osago: {
              stepsHacks: {},
              steps: {
                first: [
                  'year',
                  'car_mark',
                  'car_model_group',
                  'car_model',
                  'engine_power',
                  'owner_registration',
                  'sms_verification',
                  'calculation_type'
                ],
                second: [
                  'drivers_count'
                ]
              }
            },
            casco: {
              stepsHacks: {
              },
              steps: {
                first: [
                  'casco_all',
                  'casco_car_mark',
                  'casco_year',
                  'casco_car_model_group',
                  'casco_car_model',
                  'casco_car_other'
                ],
                second: [
                  'casco_insurance_params',
                  'drivers'
                ]
              }
            }
          };

          return params[moduleType];
        }
      ]);
}(angular));