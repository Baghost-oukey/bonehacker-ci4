// --- INIT SCRIPT ---
if (window.$) {
    window.$(document).ready(function ($) {
        if (!$('#statisticTable').length) return;
        const config = window.statistikResultConfig;
        if (!config || typeof window.moment === "undefined") return;

        $('#regionSelect').select2({
            width: '100%',
            placeholder: "Pilih Wilayah",
            allowClear: true
        });
        var currentFilter = 'daily';
        var previousStartDate = window.moment().subtract(29, 'days');
        var previousEndDate = window.moment();


        // --- TABLE DATA PASIEN ---
        var table = $('#statisticTable').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "info": true,
            "searching": true,
            "destroy": true,
            "dom": '<"w-full"t><"flex flex-col md:flex-row items-center justify-between p-5 bg-white border-t border-slate-100 gap-4"<"text-xs font-semibold text-slate-500"i><"flex items-center justify-end"p>>',
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Cari hasil...",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "previous": '<i class="fas fa-chevron-left text-[10px]"></i>',
                    "next": '<i class="fas fa-chevron-right text-[10px]"></i>'
                }
            },
            "columnDefs": [
                {
                    "targets": 0,
                    "className": "px-6 py-3 align-middle border-b border-slate-50/50",
                    "render": function (data, type) {
                        if (type === 'display' && data) {
                            return `<span class="text-[13px] font-normal text-slate-700">${data}</span>`;
                        }
                        return data;
                    }
                },
                {
                    "targets": 1,
                    "className": "px-6 py-3.5 align-middle text-center border-b border-slate-50",
                    "render": function (data, type) {
                        if (type === 'display' && data !== null) {
                            return `<span class="inline-flex items-center justify-center min-w-10 px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">${data}</span>`;
                        }
                        return data;
                    }
                }
            ],
            "initComplete": function () {
                var searchInput = $('.dataTables_filter input');
                if (searchInput.length) {
                    searchInput.addClass('w-full sm:w-64 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-slate-500/20 transition-all m-0 shadow-sm');
                    $('.dataTables_filter').appendTo('#custom-search-container');
                    $('.dataTables_filter label').contents().filter(function () { return this.nodeType === 3; }).remove();
                }
            },
            "drawCallback": function () {
                $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-end gap-1');
                $('.dataTables_paginate > span').addClass('!flex !flex-row !items-center gap-1');
                $('.paginate_button').addClass('!inline-flex items-center justify-center min-w-[32px] h-8 rounded-md border border-slate-200 text-xs font-semibold text-slate-600 cursor-pointer hover:bg-slate-50 transition-all !m-0 !p-0');
                $('.paginate_button.current').addClass('!bg-slate-800 !text-white !border-slate-800 hover:!bg-slate-900').removeClass('bg-white text-slate-600');
                $('.paginate_button.disabled').addClass('!opacity-40 cursor-not-allowed hover:!bg-white hover:!text-slate-600 hover:!border-slate-200 shadow-none');
                $('#statisticTable tbody tr').addClass('hover:bg-slate-50/80 transition-colors');
            }
        });


        // --- DATERANGEPICKER  ---
        $('#rangefilter').daterangepicker({
            locale: { format: 'D MMMM YYYY' },
            opens: 'left',
            linkedCalendars: false,
            showDropdowns: true,
            ranges: {
                'Hari Ini': [window.moment(), window.moment()],
                'Kemarin': [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
                '7 Hari Terakhir': [window.moment().subtract(6, 'days'), window.moment()],
                '30 Hari Terakhir': [window.moment().subtract(29, 'days'), window.moment()],
                'Bulan Ini': [window.moment().startOf('month'), window.moment().endOf('month')],
                'Bulan Lalu': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')]
            }
        }, function (start, end) {
            cb(start, end);
        });

        function cb(start, end) {
            $('#rangefilter span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            previousStartDate = start;
            previousEndDate = end;
            updateHeading(start, end, currentFilter);
            fetchStatistics(start, end, currentFilter);
        }
        cb(previousStartDate, previousEndDate);


        // --- LISTENERS ---
        $('#selectfilter').on('change', function () {
            currentFilter = $(this).val();
            let start, end;
            if (currentFilter === 'daily') {
                start = previousStartDate; end = previousEndDate;
            } else if (currentFilter === 'monthly') {
                start = window.moment(previousStartDate).startOf('month'); end = window.moment(previousEndDate).endOf('month');
            } else if (currentFilter === 'yearly') {
                start = window.moment(previousStartDate).startOf('year'); end = window.moment(previousEndDate).endOf('year');
            }
            $('#rangefilter').data('daterangepicker').setStartDate(start);
            $('#rangefilter').data('daterangepicker').setEndDate(end);
            cb(start, end);
        });

        $('#regionSelect').on('change', function () {
            fetchStatistics(previousStartDate, previousEndDate, currentFilter);
        });

        function updateHeading(startDate, endDate, filter) {
            let headingText = "";
            if (filter === 'daily') {
                headingText = startDate.isSame(endDate, 'day') ? startDate.format('D MMM YYYY') : startDate.format('D MMM YYYY') + ' - ' + endDate.format('D MMM YYYY');
            } else if (filter === 'monthly') {
                headingText = startDate.isSame(endDate, 'month') ? startDate.format('MMMM YYYY') : startDate.format('MMMM YYYY') + ' - ' + endDate.format('MMMM YYYY');
            } else if (filter === 'yearly') {
                headingText = startDate.isSame(endDate, 'year') ? startDate.format('YYYY') : startDate.format('YYYY') + ' - ' + endDate.format('YYYY');
            }
            $('#heading').text(headingText);
        }


        // --- AMBIL DATA ---
        function fetchStatistics(startDate, endDate, filter) {
            var region = $('#regionSelect').val();
            $.ajax({
                url: config.fetchUrl,
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: region
                },
                dataType: 'json',
                success: function (data) {
                    table.clear();
                    var rows = [];
                    if (Array.isArray(data)) {
                        data.forEach(function (item) {
                            let name = item.tagName || item.name;
                            let total = parseInt(item.total) || 0;
                            if (total > 0) rows.push([name, total]);
                        });
                    } else if (typeof data === 'object' && data !== null) {
                        Object.keys(data).forEach(function (tagName) {
                            let total = (typeof data[tagName] === 'object') ? (data[tagName].total || 0) : data[tagName];
                            if (total > 0) rows.push([tagName, total]);
                        });
                    }

                    if (rows.length > 0) {
                        table.rows.add(rows).draw();
                    } else {
                        table.draw();
                    }
                },
                error: function (xhr) {
                    console.error("Error fetch statistics:", xhr.responseText);
                }
            });
        }
    });
}