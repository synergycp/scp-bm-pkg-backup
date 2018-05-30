(function () {
    'use strict';

    var INPUTS = {
        name: '',
        handler: '',
        fields: {}
    };

    angular
        .module('pkg.backup.source')
        .component('sourceForm', {
            require: {},
            bindings: {
                form: '='
            },
            controller: 'SourceFormCtrl as sourceForm',
            transclude: true,
            templateUrl: formTemplateUrl
        })
        .controller('SourceFormCtrl', SourceFormCtrl);

    /**
     * @ngInject
     */
    function formTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/source/source.form.html');
    }

    /**
     * @ngInject
     */
    function SourceFormCtrl(_, Select) {
        var sourceForm = this;

        sourceForm.$onInit = init;
        sourceForm.input = _.clone(INPUTS);
        sourceForm.handlers = Select('pkg/backup/handler?type=source');

        (sourceForm.handlers.on || function () {
        })(['change', 'load'], function (response) {
            sourceForm.input.fields = {};
        });

        //////////

        function init() {
            fillFormInputs();
            sourceForm.form.getData = getData;
            (sourceForm.form.on || function () {
            })(['change', 'load'], function (response) {
                fillFormInputs();
                sourceForm.handlers.selected = response.handler;
                sourceForm.input.fields = response.handler.fields;
            });
        }

        function fillFormInputs() {
            _.overwrite(sourceForm.input, sourceForm.form.input);
        }

        function getData() {
            return _.clone(sourceForm.input);
        }
    }
})();
