 <style>
     /* CSS agar dropdown rapi dan bisa di-scroll */
     .dropdown-list-content.dropdown-list-icons {
         max-height: 350px !important;
         overflow-y: auto !important;
         scrollbar-width: thin;
     }

     .dropdown-item-active {
         background-color: #f8f9fa !important;
         border-left: 4px solid #6777ef !important;
     }

     .dropdown-item-desc b {
         text-transform: uppercase;
         letter-spacing: 0.5px;
     }

     .btn-switch-region {
         display: flex;
         align-items: center;
         width: 100%;
         padding: 12px 15px;
         border: none;
         cursor: pointer;
         background: transparent;
         transition: all 0.2s ease;
         font-family: inherit;
         font-size: inherit;
     }

     .btn-switch-region:hover {
         background-color: #f8f9fa;
     }

     .btn-switch-region.active {
         background-color: #f0f3ff;
         border-left: 4px solid #6777ef;
     }

     .btn-switch-region:not(.active) {
         border-left: 4px solid transparent;
     }

     .region-icon {
         width: 35px;
         height: 35px;
         border-radius: 50%;
         color: #fff;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-right: 12px;
         flex-shrink: 0;
         font-size: 14px;
     }

     .region-icon.global {
         background-color: #6777ef;
     }

     .region-icon.branch {
         background-color: #3abaf4;
     }

     .region-name {
         text-align: left;
     }

     .region-name b {
         display: block;
         font-size: 12px;
         color: #34395e;
         text-transform: uppercase;
         letter-spacing: 0.5px;
         font-weight: 700;
     }

     .region-mode {
         font-size: 10px;
         color: #98a6ad;
         font-weight: 600;
     }

     .dropdown-menu-btn {
         display: flex;
         align-items: center;
         border: none;
         background: transparent;
         width: 100%;
         cursor: pointer;
         padding: 10px 16px;
         font-weight: 500;
         font-size: 13px;
         color: #666;
         transition: background 0.2s;
         text-decoration: none;
         font-family: inherit;
     }

     .dropdown-menu-btn i {
         width: 18px;
         margin-right: 10px;
         font-size: 14px;
         text-align: center;
     }

     .dropdown-menu-btn:hover {
         background-color: #f8f9fa;
         color: #6777ef;
     }

     .dropdown-menu-btn-danger {
         color: #fc544b;
     }

     .dropdown-menu-btn-danger:hover {
         background-color: #fff5f5;
         color: #d9534f;
     }
 </style>

 <header class="navbar navbar-expand-lg bg-primary">
     <form class="form-inline mr-auto">
         <ul class="navbar-nav mr-3">
             <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
         </ul>
     </form>
     <ul class="navbar-nav navbar-right">
         <?php if (session()->get('role') === 'owner' || session()->get('role') === 'superadmin'): ?>
             <li class="dropdown dropdown-list-toggle">
                 <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg" title="Pindah Cabang">
                     <i class="fas fa-map-marker-alt"></i>
                     <span class="d-none d-lg-inline-block ml-1" style="font-size: 13px; font-weight: 600;">
                         <?= (session()->get('active_region') == 'all') ? 'Cabang' : 'Cabang: ' . (session()->get('active_region_name') ?? 'Terpilih') ?>
                     </span>
                 </a>

                 <div class="dropdown-menu dropdown-list dropdown-menu-right" style="width: 280px; border-radius: 8px; overflow: hidden;">
                     <div class="dropdown-header" style="padding: 10px 15px; font-weight: 700; text-transform: uppercase; color: #98a6ad; font-size: 10px;">Pilih Cabang Pantauan</div>

                     <div class="custom-scroll-content" style="max-height: 350px; overflow-y: auto !important; overflow-x: hidden !important;">

                         <?php $isAllActive = (session()->get('active_region') == 'all'); ?>
                         <button type="button"
                             class="btn-switch-region <?= $isAllActive ? 'active' : '' ?>"
                             onclick="switchRegion(this, event)"
                             onfocus="this.blur()"
                             data-id="all"
                             data-name="Semua Wilayah"
                             style="display: flex; align-items: center; width: 100%; padding: 12px 15px; border: none; background: <?= $isAllActive ? '#f0f3ff' : 'transparent' ?>; cursor: pointer; border-left: 4px solid <?= $isAllActive ? '#6777ef' : 'transparent' ?>; position: relative; z-index: 10;">
                             <div style="width: 35px; height: 35px; border-radius: 50%; background: #6777ef; color: #fff; display: flex; align-items: center; justify-content: center; margin-right: 12px; pointer-events: none;">
                                 <i class="fas fa-globe"></i>
                             </div>
                             <div style="text-align: left; pointer-events: none;">
                                 <b style="display: block; font-size: 12px; color: #34395e;">SEMUA CABANG</b>
                                 <div style="font-size: 10px; color: #98a6ad;">MODE GLOBAL</div>
                             </div>
                         </button>

                         <?php
                            $all_regions = session()->get('list_regions_global') ?? [];
                            foreach ($all_regions as $rg):
                                $isActive = (session()->get('active_region') == $rg['id']);
                            ?>
                             <button type="button"
                                 class="btn-switch-region <?= $isActive ? 'active' : '' ?>"
                                 onclick="switchRegion(this, event)"
                                 onfocus="this.blur()"
                                 data-id="<?= $rg['id'] ?>"
                                 data-name="<?= $rg['name'] ?>"
                                 style="display: flex; align-items: center; width: 100%; padding: 12px 15px; border: none; background: <?= $isActive ? '#f0f3ff' : 'transparent' ?>; cursor: pointer; border-left: 4px solid <?= $isActive ? '#6777ef' : 'transparent' ?>; position: relative; z-index: 10;">

                                 <div style="width: 35px; height: 35px; border-radius: 50%; background: #3abaf4; color: #fff; display: flex; align-items: center; justify-content: center; margin-right: 12px; pointer-events: none;">
                                     <i class="fas fa-building"></i>
                                 </div>
                                 <div style="text-align: left; pointer-events: none;">
                                     <b style="display: block; font-size: 12px; color: #34395e;"><?= strtoupper($rg['name']) ?></b>
                                     <div style="font-size: 10px; color: #98a6ad;">MODE CABANG</div>
                                 </div>
                             </button>
                         <?php endforeach; ?>
                     </div>
                 </div>
             </li>
         <?php endif; ?>
         <li class="dropdown">
             <a href="javascript:void(0)" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                 <img src="<?= base_url('assets/img/avatar/default.png') ?>" class="rounded-circle mr-1">
                 <div class="d-sm-none d-lg-inline-block">Hi, <?= $realname ?? 'User' ?> </div>
             </a>
             <div class="dropdown-menu dropdown-menu-right" style="width: 180px; padding: 5px 0; border-radius: 8px; border: 1px solid #ebedef; box-shadow: 0 4px 8px rgba(0,0,0,0.03); overflow: hidden;">

                 <button type="button" id="editAccountBtn" data-toggle="modal" data-target="#editAccountModal" onfocus="this.blur()" class="dropdown-menu-btn">
                     <i class="far fa-user"></i>
                     <span>Akun Saya</span>
                 </button>

                 <div style="margin: 4px 0; border-top: 1px solid #f1f1f1;"></div>

                 <a href="<?= site_url('auth/destroy') ?>" onfocus="this.blur()" class="dropdown-menu-btn dropdown-menu-btn-danger">
                     <i class="fas fa-sign-out-alt"></i>
                     <span>Keluar</span>
                 </a>
             </div>
         </li>
     </ul>
 </header>

 <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
     <div class="modal-dialog" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="editAccountModalLabel">Edit Akun</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <form id="editAccountForm">
                 <?= csrf_field() ?>
                 <div class="modal-body">
                     <div id="accountAlert" class="alert" style="display: none;"></div>
                     <div class="form-group">
                         <label for="realname">Nama Lengkap</label>
                         <input type="text" class="form-control" id="realname" name="realname" value="<?= session()->get('realname') ?>" required>
                     </div>
                     <div class="form-group">
                         <label for="username">Username</label>
                         <input type="text" class="form-control" id="username" name="username" value="<?= session()->get('username') ?>" required>
                     </div>
                     <div class="form-group">
                         <label for="password">Password (Kosongkan jika tidak ingin mengganti)</label>
                         <input type="password" class="form-control" id="password" name="password">
                     </div>
                     <input type="hidden" id="user_id" name="user_id" value="<?= session()->get('userId') ?>">
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                     <button type="button" onclick="update_profile()" id="btn-save-akun" class="btn btn-primary">Simpan</button>
                 </div>
             </form>
         </div>
     </div>
 </div>

 <script>
    // Update Akun Saya
     function update_profile() {
         const btn = $('#btn-save-akun');
         const form = $('#editAccountForm');

         const csrfToken = $('meta[name="csrf-token"]').attr('content');
         const csrfHeader = $('meta[name="csrf-header"]').attr('content');
         var formData = form.serialize();

         Swal.fire({
             title: 'Simpan Perubahan?',
             text: "Data profil Anda akan diperbarui.",
             icon: 'question',
             showCancelButton: true,
             confirmButtonColor: '#6777ef', 
             cancelButtonColor: '#d33',
             confirmButtonText: 'Ya, Simpan!',
             cancelButtonText: 'Batal'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     url: '<?= site_url("users/update_acount_users") ?>',
                     type: 'POST',
                     headers: {
                         [csrfHeader]: csrfToken
                     },
                     data: formData,
                     dataType: 'json',
                     beforeSend: function() {
                         btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
                         Swal.fire({
                             title: 'Memproses...',
                             text: 'Mohon tunggu sebentar',
                             allowOutsideClick: false,
                             didOpen: () => {
                                 Swal.showLoading()
                             }
                         });
                     },
                     success: function(response) {
                         if (response.status === 'success') {
                             $('.nav-link-user .d-lg-inline-block').text('Hi, ' + response.realname);

                             Swal.fire({
                                 icon: 'success',
                                 title: 'Berhasil!',
                                 text: response.message,
                                 showConfirmButton: false,
                                 timer: 1500
                             }).then(() => {
                                 $('#editAccountModal').modal('hide');
                                 location.reload();
                             });
                         } else {
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Gagal!',
                                 text: response.message
                             });

                             btn.prop('disabled', false).text('Simpan');

                             if (response.csrfHash) {
                                 $('meta[name="csrf-token"]').attr('content', response.csrfHash);
                             }
                         }
                     },
                     error: function(xhr) {
                         console.error("DEBUG: Error AJAX: ", xhr.responseText);
                         Swal.fire({
                             icon: 'error',
                             title: 'Oops...',
                             text: 'Terjadi kesalahan sistem atau sesi Anda telah berakhir.'
                         });
                         btn.prop('disabled', false).text('Simpan');
                     }
                 });
             }
         });
     }

    //  Fungsi Switch Region Cabang Untuk Super admin dan owner
     function switchRegion(el, e) {
         e.preventDefault();
         e.stopPropagation();

         if ($.fn.niceScroll) {
             $(".dropdown-list-content").getNiceScroll().remove();

             $(".dropdown-list-content").css({
                 'overflow-y': 'auto',
                 'overflow-x': 'hidden'
             });
         }

         const $this = $(el);
         const id = $this.attr('data-id');
         const name = $this.attr('data-name');
         const csrfToken = $('#csrf-token').attr('content');
         const csrfHeader = $('#csrf-header').attr('content');

         if (!csrfToken || !csrfHeader) {
             console.error('CSRF tokens not found!');
             return false;
         }

         $this.css('opacity', '0.5').prop('disabled', true);

         $.ajax({
             url: '<?= site_url("auth/switch_region") ?>',
             type: 'POST',
             data: {
                 region_id: id,
                 region_name: name,
                 [csrfHeader]: csrfToken
             },
             dataType: 'json',
             success: function(response) {
                 if (response.status === 'success') {
                     window.location.reload();
                 } else {
                     alert('Gagal: ' + response.message);
                     $this.css('opacity', '1').prop('disabled', false);
                 }
             },
             error: function(xhr) {
                 console.error('Error:', xhr.responseText);
                 window.location.reload();
             }
         });
     }

    //  Nggak Kepake
    //  $(document).ready(function() {
        
    //      $('#editAccountForm').on('submit', function(event) {
    //          event.preventDefault();
    //          event.stopPropagation(); 

    //          console.log("Tombol simpan ditekan!");
    //          $('#accountAlert').removeClass('alert-success alert-danger').hide();

    //          var formData = $(this).serialize();
    //          const csrfToken = $('meta[name="csrf-token"]').attr('content');
    //          const csrfHeader = $('meta[name="csrf-header"]').attr('content');

    //          let headers = {};
    //          if (csrfHeader && csrfToken) {
    //              headers[csrfHeader] = csrfToken;
    //          }

    //          $.ajax({
    //              url:
    //              type: 'POST',
    //              headers: headers,
    //              data: formData,
    //              dataType: 'json',
    //              success: function(response) {
    //                  if (response.status === 'success') {
    //                      $('#accountAlert').addClass('alert-success').text(response.message).show();
    //                      $('.nav-link-user .d-lg-inline-block').text('Hi, ' + response.realname);

    //                      setTimeout(function() {
    //                          $('#editAccountModal').modal('hide');
    //                          location.reload();
    //                      }, 1000);
    //                  } else {
    //                      $('#accountAlert').addClass('alert-danger').text(response.message).show();
    //                  }
    //              },
    //              error: function(xhr, status, error) {
    //                  $('#accountAlert').addClass('alert-danger').text('Gagal memperbarui akun.').show();
    //              }
    //          });
    //      });

    //      $('#editAccountModal').on('hidden.bs.modal', function() {
    //          $('#editAccountForm')[0].reset();
    //          $('#accountAlert').hide();
    //      });


    //  });
 </script>