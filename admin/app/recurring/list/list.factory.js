(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring')
        .factory('RecurringList', RecurringListFactory);

    /**
     * RecurringList Factory
     *
     * @ngInject
     */
    function RecurringListFactory(List, ListConfirm, RouteHelpers) {
        return function () {
            var pkg = RouteHelpers.package('backup');
            var list = List(pkg.api().all('recurring'));

            list.confirm = ListConfirm(list, 'pkg.backup.admin.recurring.modal.delete');

            list.bulk.add('Delete', list.confirm.delete);

            return list;

        };
    }
})();
