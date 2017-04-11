(function () {
    'use strict';

    var INPUTS = {
        source_id: '',
        destination_id: '',
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
            templateUrl: tableTemplateUrl
        })
        .controller('ArchiveFormCtrl', ArchiveFormCtrl)
    ;

    /**
     * @ngInject
     */
    function tableTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/archive/archive.form.html')
            ;
    }

    /**
     * @ngInject
     */
    function ArchiveFormCtrl(_, Api) {
        var archiveForm = this;

        var $destinations = Api.all('pkg/backup/destination');
        var $sources = Api.all('pkg/backup/source');

        archiveForm.input = _.clone(INPUTS);

        archiveForm.destinations = {
            items: [],
            load: loadDestinations,
        };

        archiveForm.sources = {
            items: [],
            load: loadSources,
        };

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
            var data = _.clone(archiveForm.input);

            data.source_id = archiveForm.input.source ? archiveForm.input.source.id : null;
            data.destination_id = archiveForm.input.destination ? archiveForm.input.destination.id : null;

            return data;
        }

        function loadDestinations(search) {
            return $destinations
                .getList({q: search})
                .then(storeDestinations)
                ;
        }

        function storeDestinations(destinations) {
            _.setContents(archiveForm.destinations.items, destinations);
        }

        function loadSources(search) {
            return $sources
                .getList({q: search})
                .then(storeSources)
                ;
        }

        function storeSources(sources) {
            _.setContents(archiveForm.sources.items, sources);
        }

    }
})();
