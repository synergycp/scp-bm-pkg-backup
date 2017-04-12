(function () {
    'use strict';

    angular
        .module('pkg.backup.archive')
        .factory('ArchiveList', ArchiveListFactory);

    /**
     * ArchiveList Factory
     *
     * @ngInject
     */
    function ArchiveListFactory(List, ListConfirm, RouteHelpers) {
        return function () {
            var pkg = RouteHelpers.package('backup');
            var list = List(pkg.api().all('archive'));

            list.confirm = ListConfirm(list, 'archive.modal.delete');

            list.bulk.add('Delete', list.confirm.delete);

            return list;

        };
    }
})();
