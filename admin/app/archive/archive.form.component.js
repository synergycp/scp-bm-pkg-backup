(function () {
    'use strict';

    var INPUTS = {
        source: '',
        destination: '',
    };

    angular
        .module('pkg.backup.archive')
        .component('archiveForm', {
            require: {},
            bindings: {
                form: '=',
            },
            controller: 'ArchiveFormCtrl as archiveForm',
            transclude: true,
            templateUrl: formTemplateUrl
        })
        .controller('ArchiveFormCtrl', ArchiveFormCtrl)
    ;

    /**
     * @ngInject
     */
    function formTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/archive/archive.form.html')
            ;
    }

    /**
     * @ngInject
     */
    function ArchiveFormCtrl(_, Select) {
        var archiveForm = this;

        archiveForm.input = _.clone(INPUTS);
        archiveForm.destinations = Select('pkg/backup/destination');
        archiveForm.sources = Select('pkg/backup/source');
        archiveForm.recurrings = Select('pkg/backup/recurring');

        archiveForm.$onInit = init;

        //////////

        function init() {
            fillFormInputs();
            archiveForm.form.getData = getData;
            (archiveForm.form.on || function () {
            })(['change', 'load'], function (response) {
                fillFormInputs();

                _.setContents(archiveForm.groups.selected, response.groups);
            });
        }

        function fillFormInputs() {
            _.overwrite(archiveForm.input, archiveForm.form.input);
        }

        function getData() {
            return _.clone(archiveForm.input);
        }
    }
})();
