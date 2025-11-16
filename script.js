document.addEventListener("DOMContentLoaded", function () {
  // Logic for the booking form tabs (One-way/Round-trip)
  const bookingForm = document.getElementById("bookingForm");
  if (bookingForm) {
    const flightTypeInput = document.getElementById("flight_type");
    const returnDateGroup = document.getElementById("return-date-group");
    const returnDateInput = document.getElementById("return-date");

    bookingForm.addEventListener("click", function (e) {
      if (e.target.classList.contains("tab-btn")) {
        // Update active tab style
        bookingForm.querySelector(".tab-btn.active").classList.remove("active");
        e.target.classList.add("active");

        const type = e.target.getAttribute("data-type");
        flightTypeInput.value = type;

        // Show/hide return date field
        if (type === "one-way") {
          returnDateGroup.style.display = "none";
          returnDateInput.removeAttribute("required");
        } else {
          returnDateGroup.style.display = "block";
          returnDateInput.setAttribute("required", "required");
        }
      }
    });
  }

  // Logic for the payment form (Card/UPI)
  const paymentForm = document.getElementById("paymentForm");
  if (paymentForm) {
    const cardDetails = document.getElementById("card-details");
    const upiDetails = document.getElementById("upi-details");
    const cardInputs = cardDetails.querySelectorAll("input");
    const upiInput = upiDetails.querySelector("input");

    paymentForm.addEventListener("change", function (e) {
      if (e.target.name === "payment_method") {
        if (e.target.value === "card") {
          cardDetails.style.display = "block";
          upiDetails.style.display = "none";
          cardInputs.forEach((input) =>
            input.setAttribute("required", "required")
          );
          upiInput.removeAttribute("required");
        } else {
          cardDetails.style.display = "none";
          upiDetails.style.display = "block";
          cardInputs.forEach((input) => input.removeAttribute("required"));
          upiInput.setAttribute("required", "required");
        }
      }
    });

    // Set initial required attributes based on the checked radio
    const checkedMethod = paymentForm.querySelector(
      'input[name="payment_method"]:checked'
    ).value;
    if (checkedMethod === "card") {
      cardInputs.forEach((input) => input.setAttribute("required", "required"));
      upiInput.removeAttribute("required");
    } else {
      cardInputs.forEach((input) => input.removeAttribute("required"));
      upiInput.setAttribute("required", "required");
    }
  }
});
