$(document).ready(function() {

  // Calendar
  $('#calendar').fullCalendar({
    hiddenDays: [ 0 ],
    locale: "fr",
    buttonText: { today: "Aujourd'hui" },
    defaultView: "agendaWeek",
    height: "parent"
  })

  // Menu
  function moveScreen (actionRoot, direction) {
    var currentShown = actionRoot.find("> div:visible").index()
    var nextToShow = (currentShown  + direction) % 3; 
    actionRoot.find("> div").hide()
    actionRoot.find("> div").eq(nextToShow).show()
  }
  $(".clickable").on("click", function () {
    var myActionRoot = $(this).parentsUntil(".action").parent()
    var direction = parseInt($(this).data("direction"))
    moveScreen(myActionRoot, direction)
  })

  // Show are hide the "Seulement le"
  $('input:radio[name="recurrence"]').change(function() {
    if (this.checked && this.value == 'ponctual')
      $('.ponctual').show()
    else
      $('.ponctual').hide()
  }) 

  // Launch the arrow animation
  $(".changeDirection").on("click", function () {
    $(this).parent().find(".direction").toggleClass("rotated")
  })

  // Hide the other action when beginning one
  $(".launchAction").on("click", function () {
    var myActionRoot = $(this).parentsUntil(".action").parent()
    $(this).parentsUntil(".verticalSplit2").parent()
      .find(".action").not(myActionRoot).find("> div").hide().eq(0).show()
  })

})