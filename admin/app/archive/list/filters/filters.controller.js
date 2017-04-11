(function () {
    'use strict';

    angular
        .module('pkg.backup.archive.list.filters')
        .controller('ArchiveFiltersCtrl', ArchiveFiltersCtrl)
    ;

    /**
     * @ngInject
     */
    function ArchiveFiltersCtrl(Select, Observable, $state, $q) {
        var filters = this;

        filters.$onInit = init;
        filters.$onChanges = $onChanges;

        filters.current = {q: $state.params.q};
        filters.status = Select()
            .addItem({id: '0', text: 'QUEUED', name: 'QUEUED'})
            .addItem({id: 1, text: 'COMPRESS', name: 'COMPRESS'})
            .addItem({id: 2, text: 'COPYING', name: 'COPYING'})
            .addItem({id: 4, text: 'FINISHED', name: 'FINISHED'})
            .addItem({id: 10, text: 'FAILED', name: 'FAILED'});

        filters.searchFocus = Observable(false);

        filters.fireChangeEvent = fireChangeEvent;

        //////////

        function init() {
            $q.all([
                filters.status.setSelectedId($state.params['status.id']),
            ]).then(listenForChanges).then(fireChangeEvent);
        }

        function listenForChanges() {
            filters.status.on('change', fireChangeEvent);
        }

        function fireChangeEvent() {
            _.assign(filters.current, {
                status: filters.status.getSelected('id'),
            });

            $state.go($state.current.name, {
                'status.id': filters.current.status,
                q: filters.current.q,
            });

            if (filters.change) {
                filters.change();
            }
        }

        function $onChanges(changes) {
            if (changes.show) {
                var onShow = filters.searchFocus.set.bind(null, true);
                (changes.show.currentValue ? onShow : angular.noop)();
            }
        }
    }
})();
