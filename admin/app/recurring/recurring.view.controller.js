(function () {
    'use strict';

    angular
        .module('app.network')
        .controller('RecurringViewCtrl', RecurringViewCtrl)
    ;

    /**
     * View Entity Controller
     *
     * @ngInject
     */
    function RecurringViewCtrl(Edit, $stateParams) {
        var vm = this;

        vm.edit = Edit('/api/pkg/backup/recurring/' + $stateParams.id);

        vm.edit.submit = submit;

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.recurring',
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
