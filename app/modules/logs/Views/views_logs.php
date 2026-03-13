<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="section">
    <div class="section-header">
        <h1>Log Details for <?= $date; ?></h1>
    </div>
    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form id="logSearchForm" action="<?= base_url('logs'); ?>" method="get" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="log_date">Select Date:</label>
                        <input type="date" name="date" id="log_date" class="form-control w-auto" style="width: 200px; margin-right: 10px;" value="<?= $date; ?>">
                    </div>
                </form>

                <div style="margin-bottom: 20px;">
                    <?php 
                        $prevDay = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                        $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                    ?>
                    <a href="<?= base_url('logs?date=' . $prevDay); ?>" class="btn btn-primary" style="margin-right: 10px;">Previous Day</a>
                    <a href="<?= base_url('logs?date=' . $nextDay); ?>" class="btn btn-primary">Next Day</a>
                </div>
                
                <div class="log-container" style="background: #f8f9fa; border: 1px solid #e3e6f0; padding: 15px; border-radius: 5px;">
                    <pre style="margin-top: 20px; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($log_content); ?></pre>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('log_date').addEventListener('change', function() {
        document.getElementById('logSearchForm').submit();
    });
</script>
<?= $this->endSection() ?>