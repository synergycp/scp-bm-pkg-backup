(function () {
    'use strict';

    angular
        .module('pkg.backup.archive')
        .controller('PkgBackupArchiveIndexCtrl', PkgBackupArchiveIndexCtrl);

    /**
     * @ngInject
     */
    function PkgBackupArchiveIndexCtrl(List, $stateParams, RouteHelpers) {
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

        activate();

        ////////////

        function activate() {
        }

        function create() {
            vm.list.create(vm.create.getData());
        }

    }
})();
