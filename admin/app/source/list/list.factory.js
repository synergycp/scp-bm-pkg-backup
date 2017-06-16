(function () {
    'use strict';

    angular
        .module('pkg.backup.source')
        .factory('SourceList', SourceListFactory);

    /**
     * SourceList Factory
     *
     * @ngInject
     */
    function SourceListFactory(List, ListConfirm, RouteHelpers) {
        return function () {
            var pkg = RouteHelpers.package('backup');
            var list = List(pkg.api().all('source'));

            list.confirm = ListConfirm(list, 'pkg.backup.admin.source.modal.delete');

            list.bulk.add('Delete', list.confirm.delete);

            return list;

        };
    }
})();
