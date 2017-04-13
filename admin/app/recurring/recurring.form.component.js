(function () {
    'use strict';

    var INPUTS = {
        source_id: '',
        source: '',
        destination_id: '',
        dest: '',
        period: '',
    };

    angular
        .module('pkg.backup.recurring')
        .component('recurringForm', {
            require: {},
            bindings: {
                form: '=',
            },
            controller: 'RecurringFormCtrl as recurringForm',
            transclude: true,
            templateUrl: formTemplateUrl
        })
        .controller('RecurringFormCtrl', RecurringFormCtrl)
    ;

    /**
     * @ngInject
     */
    function formTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/recurring/recurring.form.html')
            ;
    }

    /**
     * @ngInject
     */
    function RecurringFormCtrl(_, Select) {
        var recurringForm = this;

        recurringForm.$onInit = init;
        recurringForm.input = _.clone(INPUTS);
        recurringForm.destinations = Select('pkg/backup/destination');
        recurringForm.sources = Select('pkg/backup/source');

        //////////

        function init() {
            fillFormInputs();
            recurringForm.form.getData = getData;
            (recurringForm.form.on || function () {
            })(['change', 'load'], function (response) {
                fillFormInputs();
                recurringForm.destinations.selected = response.dest;
                recurringForm.sources.selected = response.source;
            });
        }

        function fillFormInputs() {
            _.overwrite(recurringForm.input, recurringForm.form.input);
        }

        function getData() {
            var data = _.clone(recurringForm.input);

            data.source_id = recurringForm.input.source ? recurringForm.input.source.id : null;
            data.destination_id = recurringForm.input.dest ? recurringForm.input.dest.id : null;

            return data;
        }
    }
})();
