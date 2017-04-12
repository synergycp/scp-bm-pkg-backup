(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring')
        .controller('PkgBackupRecurringIndexCtrl', PkgBackupRecurringIndexCtrl);

    /**
     * @ngInject
     */
    function PkgBackupRecurringIndexCtrl(RecurringList, RouteHelpers, ListFilter, $state) {
        var vm = this;
        var pkg = RouteHelpers.package('backup');

        vm.list = RecurringList();
        vm.create = {
            submit: create,
        };

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.recurring',
            },
        };

        vm.filters = ListFilter(vm.list);
        vm.filters.on('change', function () {
            $state.go($state.current.name, vm.filters.current);
        });

        activate();

        ////////////

        function activate() {
            vm.list.load();
        }

        function create() {
            vm.list.create(vm.create.getData());
        }

    }
})();
