$(document).ready(function () {
    console.log('Booking script loaded');

    // Handle seat selection
    $(document).on('click', '.seat', function () {
        console.log('Seat clicked');

        if ($(this).hasClass('btn-danger')) {
            alert('This seat is already booked!');
            return;
        }

        // Highlight the selected seat
        $('.seat').removeClass('btn-primary').addClass('btn-success');
        $(this).removeClass('btn-success').addClass('btn-primary');

        // Update the selected seat input and display
        const selectedSeat = $(this).data('seat');
        console.log('Selected seat:', selectedSeat);
        $('#selected_seat').val(selectedSeat);
        $('#seat_display').text(selectedSeat);

        // Show the booking form and options
        document.getElementById('bookingForm').style.display = 'block';
        document.getElementById('book-without-payment-button').disabled = false;
        
        // Make booking options visible
        var bookingOptions = document.querySelector('.payment-options');
        bookingOptions.style.display = 'block';
        
        console.log('Booking form displayed');
    });

    // Handle direct booking
    $(document).on('click', '#book-without-payment-button', function () {
        const selectedSeat = $('#selected_seat').val();
        
        if (!selectedSeat) {
            alert('Please select a seat first');
            return;
        }

        if (confirm('Confirm booking for seat ' + selectedSeat + '?')) {
            // Disable button and show loading
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Booking...');

            // Submit booking
            $.ajax({
                url: '{{ route("bookings.store") }}',
                type: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    route_id: $('input[name="route_id"]').val(),
                    customer_id: $('input[name="customer_id"]').val(),
                    selected_seat: selectedSeat,
                    price: $('input[name="price"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Booking successful!');
                        window.location.href = response.redirect_url || '/userbookings';
                    } else {
                        alert(response.message || 'Booking failed. Please try again.');
                        resetBookingButton();
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Booking failed. Please try again.';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    } catch (e) {}
                    alert(errorMessage);
                    resetBookingButton();
                }
            });
        }
    });

    function resetBookingButton() {
        $('#book-without-payment-button').prop('disabled', false).html('<i class="fas fa-ticket-alt me-2"></i>Book Now');
    }
});
