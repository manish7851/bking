/**
 * Debug utility for seat selection
 * Adds detailed logging to help debug seat selection issues
 */

$(document).ready(function() {
    console.log('Seat debug tool loaded');
    
    // Log the initial state of all seats
    const initialSeats = { 
        available: [], 
        booked: [],
        selected: []
    };
    
    $('.seat').each(function() {
        const seatId = $(this).data('seat');
        if ($(this).hasClass('btn-danger')) {
            initialSeats.booked.push(seatId);
        } else if ($(this).hasClass('btn-primary')) {
            initialSeats.selected.push(seatId);
        } else {
            initialSeats.available.push(seatId);
        }
    });
    
    console.log('Initial seat state:', initialSeats);
    console.log(`Available: ${initialSeats.available.length}, Booked: ${initialSeats.booked.length}, Selected: ${initialSeats.selected.length}`);
    
    // Log every seat click event with detailed state
    $(document).on('click', '.seat', function() {
        const seatId = $(this).data('seat');
        const isBooked = $(this).hasClass('btn-danger');
        const isSelected = $(this).hasClass('btn-primary');
        
        console.group(`Seat Click: ${seatId}`);
        console.log('Is booked?', isBooked);
        console.log('Is selected?', isSelected);
        console.log('Button classes:', $(this).attr('class'));
        console.groupEnd();
    });
    
    // Track form submission attempts
    $('#bookingForm').on('submit', function() {
        const selectedSeat = $('#selected_seat').val();
        console.log('Form submitted with selected seat:', selectedSeat);
    });
});
