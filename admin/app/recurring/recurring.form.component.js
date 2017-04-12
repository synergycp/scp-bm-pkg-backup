(function () {
    'use strict';

    var INPUTS = {
        source_id: '',
        destination_id: '',
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
    function RecurringFormCtrl(_, Api) {
        var recurringForm = this;

        var $destinations = Api.all('pkg/backup/destination');
        var $sources = Api.all('pkg/backup/source');

        recurringForm.input = _.clone(INPUTS);

        recurringForm.destinations = {
            items: [],
            load: loadDestinations,
        };

        recurringForm.sources = {
            items: [],
            load: loadSources,
        };

        recurringForm.$onInit = init;

        //////////

        function init() {
            fillFormInputs();
            recurringForm.form.getData = getData;
            (recurringForm.form.on || function () {
            })(['change', 'load'], function (response) {
                fillFormInputs();

                _.setContents(recurringForm.groups.selected, response.groups);
            });
        }

        function fillFormInputs() {
            _.overwrite(recurringForm.input, recurringForm.form.input);
        }

        function getData() {
            var data = _.clone(recurringForm.input);

            data.source_id = recurringForm.input.source ? recurringForm.input.source.id : null;
            data.destination_id = recurringForm.input.destination ? recurringForm.input.destination.id : null;

            return data;
        }

        function loadDestinations(search) {
            return $destinations
                .getList({q: search})
                .then(storeDestinations)
                ;
        }

        function storeDestinations(destinations) {
            _.setContents(recurringForm.destinations.items, destinations);
        }

        function loadSources(search) {
            return $sources
                .getList({q: search})
                .then(storeSources)
                ;
        }

        function storeSources(sources) {
            _.setContents(recurringForm.sources.items, sources);
        }

    }
})();
