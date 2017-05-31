(function () {
    'use strict';

    var INPUTS = {
        name: '',
        handler: '',
        fields: {}
    };

    angular
        .module('pkg.backup.destination')
        .component('destinationForm', {
            require: {},
            bindings: {
                form: '='
            },
            controller: 'DestinationFormCtrl as destinationForm',
            transclude: true,
            templateUrl: formTemplateUrl
        })
        .controller('DestinationFormCtrl', DestinationFormCtrl);

    /**
     * @ngInject
     */
    function formTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/destination/destination.form.html');
    }

    /**
     * @ngInject
     */
    function DestinationFormCtrl(_, Select) {
        var destinationForm = this;

        destinationForm.$onInit = init;
        destinationForm.input = _.clone(INPUTS);
        destinationForm.handlers = Select('pkg/backup/handler');

        //////////

        function init() {
            fillFormInputs();
            destinationForm.form.getData = getData;
            (destinationForm.form.on || function () {
            })(['change', 'load'], function (response) {
                fillFormInputs();
                destinationForm.handlers.selected = response.handler;
            });
        }

        function fillFormInputs() {
            _.overwrite(destinationForm.input, destinationForm.form.input);
        }

        function getData() {
            return _.clone(destinationForm.input);
        }
    }
})();
