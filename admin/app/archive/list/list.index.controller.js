(function () {
    'use strict';

    angular
        .module('pkg.backup.archive')
        .controller('PkgBackupArchiveIndexCtrl', PkgBackupArchiveIndexCtrl);

    /**
     * @ngInject
     */
    function PkgBackupArchiveIndexCtrl(List, $stateParams, RouteHelpers, ListFilter, $state) {
        var vm = this;
        var pkg = RouteHelpers.package('backup');

        vm.search = $stateParams.search || '';
        vm.list = List(pkg.api().all('archive'));
        vm.create = {
            submit: create,
        };

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.archive',
            },
        };

        vm.list.load();

        vm.filters = ListFilter(vm.list);
        vm.filters.current.q = $state.params.q;
        vm.filters.on('change', function () {
            $state.go($state.current.name, vm.filters.current);
        });

        activate();

        ////////////

        function activate() {
        }

        function create() {
            console.log(vm.create.getData())
            vm.list.create(vm.create.getData());
        }

    }
})();
