
if (window.$) {
    window.$(document).ready(function ($) {

        // --- CONNECT KE VIEWS ---
        if (!$('#statisticTable').length) return;
        var config = window.statistikConfig;
        if (!config) return;

        $('#regionSelect').select2({
            width: '150px'
        });

        var currentFilter = 'daily';
        var currentTag = 'complaint';
        var previousStartDate = moment().subtract(29, 'days');
        var previousEndDate = moment();
        var start = moment().subtract(29, 'days');
        var end = moment();


        // --- INISIASI TABLE ---
        var table = $('#statisticTable').DataTable({
            "autoWidth": false,
            "paging": true,
            "pageLength": 10,
            "ordering": true,
            "info": true,
            "searching": true,
            "destroy": true,
            "dom": '<"w-full overflow-x-auto"t><"flex flex-col md:flex-row items-center justify-between p-6 bg-white border-t border-slate-100 gap-4"<"flex flex-wrap items-center gap-4 text-xs font-bold text-slate-500"li><"flex items-center justify-end"p>>',
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Cari data keluhan...",
                "lengthMenu": "Tampil _MENU_",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "previous": '<i class="fas fa-chevron-left text-[10px]"></i>',
                    "next": '<i class="fas fa-chevron-right text-[10px]"></i>'
                }
            },
            "columnDefs": [
                {
                    "targets": 0,
                    "width": '75%',
                    "render": function (data, type, row) {
                        if (type === 'display') {
                            return `
                        <div class="flex items-center gap-3 px-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0">
                                <i class="fas fa-hashtag text-[10px]"></i>
                            </div>
                            <span class="text-sm font-normal text-slate-700">${data}</span>
                        </div>`;
                        }
                        return data;
                    }
                },
                {
                    "targets": 1,
                    "width": "25%",
                    "className": "text-center",
                    "render": function (data, type, row) {
                        if (type === 'display') {
                            return `<span class="inline-flex items-center justify-center min-w-10 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-[13px] font-medium border border-emerald-200/60">${data}</span>`;
                        }
                        return data;
                    }
                }
            ],
            "drawCallback": function () {
                $('#statisticTable tbody tr').addClass('bg-white hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-none');
                $('#statisticTable tbody td').addClass('border-0'); // Hilangkan border bawaan DataTables di sel
                $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-end gap-1');
                $('.dataTables_paginate > span').addClass('!flex !flex-row !items-center gap-1');
                $('.paginate_button').addClass('!inline-flex items-center justify-center min-w-[32px] h-8 rounded-md border border-slate-200 text-xs font-semibold text-slate-600 cursor-pointer hover:bg-indigo-50 hover:text-indigo-600 transition-all !m-0 !p-0');
                $('.paginate_button.current').addClass('!bg-indigo-600 !text-white !border-indigo-600 hover:!bg-indigo-700').removeClass('bg-white text-slate-600');
                $('.paginate_button.disabled').addClass('!opacity-40 cursor-not-allowed hover:!bg-white hover:!text-slate-600');
                var searchInput = $('.dataTables_filter input');
                if (searchInput.length) {
                    searchInput.addClass('w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[13px] font-medium text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm').attr('placeholder', 'Cari keluhan...');
                    $('.dataTables_filter').appendTo('#table-search-container').addClass('w-full sm:w-72');
                    $('.dataTables_filter label').contents().filter(function () { return this.nodeType === 3; }).remove();
                }
            }
        });

        $('.dataTables_filter input').addClass('w-full md:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500/20 mb-4 mx-6 mt-4');



        // --- KONFIGURASI UNTUK DATE PICKER ---
        $('#rangefilter').daterangepicker({
            startDate: previousStartDate,
            endDate: previousEndDate,
            opens: 'left',
            drops: 'auto',
            alwaysShowCalendars: true,
            showDropdowns: true,
            parentEl: "body",
            locale: {
                format: 'D MMMM YYYY',
                applyLabel: "Terapkan",
                cancelLabel: "Batal",
                customRangeLabel: "Rentang Kustom",
                daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                monthNames: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"]
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function (start, end) {
            $('#rangefilter span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            previousStartDate = start;
            previousEndDate = end;
            updateHeading(start, end, currentFilter);
            fetchStatistics(start, end, currentFilter, currentTag);
        });

        // Set initial label & trigger first load
        $('#rangefilter span').html(previousStartDate.format('D MMMM YYYY') + ' - ' + previousEndDate.format('D MMMM YYYY'));
        updateHeading(previousStartDate, previousEndDate, currentFilter);
        fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);

        $('#selectfilter').on('change', function () {
            currentFilter = $(this).val();
            fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
        });

        $('#selecttag').on('change', function () {
            currentTag = $(this).val();
            $('#dynamicTitle').text(currentTag === 'complaint' ? 'Statistik Keluhan' : 'Statistik Riwayat Medis');
            fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
        });

        $('#regionSelect').on('change', function () {
            fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
        });

        function updateHeading(startDate, endDate, filter) {
            moment.locale('id');
            var headingText = startDate.format('D MMM YYYY') + (startDate.isSame(endDate, 'day') ? '' : ' - ' + endDate.format('D MMM YYYY'));
            $('#heading').text(headingText);
        }

        // --- AMBIL DATA DARI CONTROLLER ---
        function fetchStatistics(startDate, endDate, filter, tag) {
            var region = $('#regionSelect').val();
            $.ajax({
                url: config.fetchUrl,
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    tag: tag,
                    region_id: region
                },
                dataType: 'json',
                beforeSend: function () {
                },
                success: function (data) {
                    table.clear();
                    var rows = [];

                    if (data && typeof data === 'object') {
                        Object.keys(data).forEach(function (tagName) {
                            let total = data[tagName].total || 0;

                            if (total > 0) {
                                rows.push([tagName, total]);
                            }
                        });
                    }

                    if (rows.length > 0) {
                        table.rows.add(rows).draw();
                    } else {
                        console.warn("Data diterima tapi tidak ada tag dengan jumlah > 0:", data);
                        table.draw();
                    }
                },
                error: function (xhr) {
                    console.error("Error fetching data:", xhr.responseText);
                }
            });
        }
    });
}