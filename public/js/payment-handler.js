const PaymentHandler = {
    validatePrerequisites: (formData) => {
        // Validate seat selection
        if (!formData.selected_seat || formData.selected_seat === '') {
            Swal.fire({
                title: 'No Seat Selected',
                text: 'Please select a seat before proceeding with payment.',
                icon: 'warning'
            });
            return false;
        }

        // Validate user login
        if (formData.is_logged_in !== '1') {
            Swal.fire({
                title: 'Login Required',
                text: 'Please login to complete your booking.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Login',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/userlogin?redirect_after_login=' + encodeURIComponent(window.location.href);
                }
            });
            return false;
        }

        return true;
    },

    handleError: (error, type = 'general') => {
        console.error('Payment error:', error);
        let title, message;

        switch (type) {
            case 'gateway':
                title = 'Payment Gateway Error';
                message = 'There was an error processing your payment. Please try again.';
                break;
            case 'validation':
                title = 'Validation Error';
                message = 'Please check your information and try again.';
                break;
            default:
                title = 'Error';
                message = 'An unexpected error occurred. Please try again.';
        }

        Swal.fire({
            title: title,
            text: message,
            icon: 'error'
        });
    }
};

const eSewaHandler = {
    process: () => {
        // We don't need this anymore as form submits directly
        return true;
    }
};

const khaltiHandler = {
    process: (formData, amount, publicKey) => {
        return new Promise((resolve, reject) => {
            if (!window.KhaltiCheckout) {
                reject(new Error('Khalti checkout not loaded'));
                return;
            }

            const config = {
                publicKey: publicKey,
                productIdentity: formData.route_id,
                productName: formData.bus_name || 'Bus Ticket',
                productUrl: window.location.href,
                amount: amount,
                eventHandler: {
                    onSuccess: (payload) => {
                        // Send verification request to the backend
                        fetch(window.khaltiVerifyUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ...formData,
                                token: payload.token,
                                amount: amount
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                reject(new Error(data.message || 'Payment verification failed'));
                            }
                        })
                        .catch(error => reject(error));
                    },
                    onError: (error) => reject(error),
                    onClose: () => resolve()
                }
            };

            const checkout = new KhaltiCheckout(config);
            checkout.show();
        });
    }
};