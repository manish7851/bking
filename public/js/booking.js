$(document).ready(function () {
    console.log('Booking script loaded');
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });    // Handle seat selection
    $(document).on('click', '.seat', function () {
        console.log('Seat clicked');
        
        // Skip if disabled - these are truly booked seats
        if ($(this).prop('disabled')) {
            return;
        }

        // Check if it's already the selected seat (clicking the same seat twice)
        if ($(this).hasClass('btn-primary')) {
            console.log('Seat already selected');
            return;
        }

        // Add a visual selecting effect
        $(this).addClass('selecting');
        
        // After a small delay to show the selection animation
        setTimeout(() => {
            // Reset all seats to their original state (green)
            $('.seat:not(.btn-danger)').removeClass('btn-primary').addClass('btn-success');
            
            // Highlight the selected seat (blue)
            $(this).removeClass('btn-success selecting').addClass('btn-primary');
        }, 200);

        // Update the selected seat input and display
        const selectedSeat = $(this).data('seat');
        console.log('Selected seat:', selectedSeat);
        $('#selected_seat').val(selectedSeat);
        $('#seat_display').text(selectedSeat);

        // Validate seat selection immediately
        if (!selectedSeat) {
            alert('Error: Could not get seat information. Please try again.');
            return;
        }

        // Check if the seat is still available through a quick API call
        $.ajax({
            url: '/check-seat-availability',
            type: 'GET',
            data: {
                route_id: $('input[name="route_id"]').val(),
                seat: selectedSeat
            },
            success: function(response) {
                if (!response.available) {
                    alert('This seat has already been booked. Please select another seat.');
                    // Mark the seat as booked
                    $('button[data-seat="' + selectedSeat + '"]')
                        .removeClass('btn-primary btn-success')
                        .addClass('btn-danger')
                        .prop('disabled', true);
                    // Clear the selection
                    $('#selected_seat').val('');
                    $('#seat_display').text('None');
                }
            },
            error: function() {
                // If there's an error, continue anyway - we'll validate on submission
                console.log('Could not verify seat availability in real-time');
            }
        });        // Show the booking form and options
        $('#bookingForm').show();
        
        // Check if user is logged in
        const isLoggedIn = $('input[name="is_logged_in"]').val() === '1';
        if (!isLoggedIn) {
            // Show login prompt for guest users
            const loginPrompt = `
                <div class="alert alert-warning mt-3" id="seat-login-prompt">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Login Required</h6>
                    <p class="mb-2">To book seat <strong>${selectedSeat}</strong>, please login first.</p>
                    <div class="d-flex gap-2">
                        <a href="/userlogin?redirect_after_login=${encodeURIComponent(window.location.href)}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a href="/userregister" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </div>
                </div>
            `;
            
            // Remove any existing login prompt
            $('#seat-login-prompt').remove();
            
            // Add login prompt after the booking summary
            $('.booking-summary').after(loginPrompt);
            
            // Hide the payment methods and submit button for guest users
            $('.payment-methods, #book-btn').hide();
        } else {
            // User is logged in, remove any login prompts and show booking options
            $('#seat-login-prompt').remove();
            $('.payment-methods, #book-btn').show();
        }
    });    // Handle payment method selection
    $('input[name="payment_method_radio"]').change(function() {
        const selectedMethod = $(this).val();
        $('#payment_method').val(selectedMethod);
        
        // Update button text based on payment method
        const btnText = $('#btn-text');
        if (selectedMethod === 'esewa') {
            btnText.text('Pay with eSewa');
        } else {
            btnText.text('Book Seat');
        }
    });    // Handle booking form submission
    $('#actualBookingForm').submit(function(e) {
        e.preventDefault();
        
        // Validate seat selection before submission
        const selectedSeat = $('#selected_seat').val();
        if (!selectedSeat || selectedSeat === '' || selectedSeat === 'None') {
            alert('Please select a seat first');
            return false;
        }
        
        // Check if user is logged in before submission
        const isLoggedIn = $('input[name="is_logged_in"]').val() === '1';
        if (!isLoggedIn) {
            const loginUrl = '/userlogin?redirect_after_login=' + encodeURIComponent(window.location.href);
            showLoginModal('Please login to complete your booking', loginUrl);
            return false;
        }
        
        const paymentMethod = $('#payment_method').val();
        if (paymentMethod === 'khalti') {
            // Prevent normal form submission for Khalti
            const amount = parseFloat($('input[name="price"]').val()) * 100;
            const publicKey = window.khaltiPublicKey || '';
            const bookingData = $(this).serializeArray();
            var khaltiConfig = {
                publicKey: publicKey,
                productIdentity: $('input[name="route_id"]').val(),
                productName: $('input[name="bus_name"]').val() || 'Bus Ticket',
                productUrl: window.location.href,
                eventHandler: {
                    onSuccess: function(payload) {
                        const postData = {};
                        bookingData.forEach(function(item) { postData[item.name] = item.value; });
                        postData['khalti_token'] = payload.token;
                        postData['amount'] = amount;
                        $.ajax({
                            url: window.khaltiVerifyUrl || '',
                            method: 'POST',
                            data: postData,
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(response) {
                                if (response.success && response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else if (response.success) {
                                    alert('Payment successful!');
                                    window.location.reload();
                                } else {
                                    alert(response.message || 'Khalti payment verification failed.');
                                }
                            },
                            error: function(xhr) {
                                alert('Khalti payment verification failed.');
                            }
                        });
                    },
                    onError: function(error) {
                        alert('Khalti payment error: ' + (error.message || error));
                    },
                    onClose: function() {
                        // User closed Khalti widget
                    }
                }
            };
            var checkout = new KhaltiCheckout(khaltiConfig);
            checkout.show({amount: amount});
            return false;
        }
        
        // Disable submit button and show loading
        const submitBtn = $('#book-btn');
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
        
        // Submit booking
        $.ajax({
            url: bookingStoreUrl,
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        window.location.href = '/userdashboard';
                    }
                } else {
                    alert(response.message || 'Booking failed. Please try again.');
                    resetBookButton();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Booking failed. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.redirect_to_login) {
                        showLoginModal(errorResponse.message, errorResponse.login_url || '/userlogin');
                        resetBookButton();
                        return;
                    }
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Response text:', xhr.responseText);
                }
                alert(errorMessage);
                resetBookButton();
            }
        });
    });
      function resetBookButton() {
        const btn = $('#book-btn');
        btn.prop('disabled', false)
            .html('<span id="btn-text">' + 
                ($('input[name="payment_method_radio"]:checked').val() === 'esewa' ? 
                'Pay with eSewa' : 'Book Seat') + 
                '</span>');
    }

    function showLoginModal(message, loginUrl) {
        // Remove any existing modal
        $('#loginModal').remove();
        
        const modalHtml = `
            <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loginModalLabel">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Required
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                            <p>You need to login to complete your booking. Your selected seat will be saved.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="${loginUrl}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Login Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        $('#loginModal').modal('show');
    }
});

// Set bookingStoreUrl from Blade if not already set
if (typeof bookingStoreUrl === 'undefined') {
    window.bookingStoreUrl = window.bookingStoreUrl || '';
}
