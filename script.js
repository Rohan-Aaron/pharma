$(document).ready(function() {
    let deleteId;
    
    $('.delete-btn').click(function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: 'delete_medicine.php',
            method: 'POST',
            data: { 
                id: deleteId,
                csrf_token: '<?= $_SESSION['csrf_token'] ?>' 
            },
            success: function(response) {
                if(response === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.statusText);
            }
        });
    });
});