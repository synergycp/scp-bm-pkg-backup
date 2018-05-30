(function () {
    'use strict';

    angular
        .module('app.network')
        .controller('SourceViewCtrl', SourceViewCtrl)
    ;

    /**
     * View Entity Controller
     *
     * @ngInject
     */
    function SourceViewCtrl(Edit, $stateParams) {
        var vm = this;

        vm.edit = Edit('/pkg/backup/source/' + $stateParams.id);

        vm.edit.submit = submit;

        vm.logs = {
            filter: {
                target_type: 'pkg.backup.source',
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
