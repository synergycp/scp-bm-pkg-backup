(function () {
    'use strict';

    angular
        .module('pkg.backup.archive')
        .controller('PkgBackupArchiveIndexCtrl', PkgBackupArchiveIndexCtrl);

    /**
     * @ngInject
     */
    function PkgBackupArchiveIndexCtrl(ArchiveList, RouteHelpers, ListFilter, $state) {
        var vm = this;
        var pkg = RouteHelpers.package('backup');

        vm.list = ArchiveList();
        vm.create = {
            submit: create,
        };

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.archive',
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
