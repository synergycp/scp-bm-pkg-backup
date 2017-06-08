(function () {
    'use strict';

    angular
        .module('app.network')
        .controller('DestinationViewCtrl', DestinationViewCtrl)
    ;

    /**
     * View Entity Controller
     *
     * @ngInject
     */
    function DestinationViewCtrl(Edit, $stateParams) {
        var vm = this;

        vm.edit = Edit('/pkg/backup/destination/' + $stateParams.id);

        vm.edit.submit = submit;

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.destination',
                target_id: $stateParams.id,
            },
        };

        activate();

        //////////

        function activate() {
            vm.edit.getCurrent();
        }

        function submit() {
            vm.edit.patch(vm.edit.getData());
        }
    }
})();
