(function () {
    'use strict';

    angular
        .module('pkg.backup.destination')
        .factory('DestinationList', DestinationListFactory);

    /**
     * DestinationList Factory
     *
     * @ngInject
     */
    function DestinationListFactory(List, ListConfirm, RouteHelpers) {
        return function () {
            var pkg = RouteHelpers.package('backup');
            var list = List(pkg.api().all('destination'));

            list.confirm = ListConfirm(list, 'pkg.backup.admin.destination.modal.delete');

            list.bulk.add('Delete', list.confirm.delete);

            return list;

        };
    }
})();
