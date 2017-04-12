(function () {
    'use strict';

    angular
        .module('pkg.backup.archive.list.filters')
        .component('archiveFilters', {
            require: {
                list: '\^list',
            },
            bindings: {
                show: '<',
                current: '=',
                change: '&?',
            },
            controller: 'ArchiveFiltersCtrl as filters',
            transclude: true,
            templateUrl: filterTemplateUrl
        })
    ;

    /**
     * @ngInject
     */
    function filterTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/archive/list/filters/filters.html')
            ;
    }
})();
