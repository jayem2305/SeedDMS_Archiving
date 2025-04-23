

<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Remove backdrop immediately when modal is closed
        $('#searchModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').fadeOut(0, function () { 
                $(this).remove(); 
            });
        });

        // Prevent multiple backdrops from stacking
        $('#searchModal').on('shown.bs.modal', function () {
            $('.modal-backdrop').not(':last').remove();
        });
    });
</script>
