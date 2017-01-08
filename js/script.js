$(document).ready(function() {

  // Do not save any inputs, fix FF bug
  $("input[checked='checked']").each(function() {
    $(this).prop('checked', true)
  })

  // Calendar
  $('#calendar').fullCalendar({
    hiddenDays: [ 0 ],
    locale: "fr",
    buttonText: { today: "Aujourd'hui" },
    defaultView: "agendaWeek",
    height: "parent"
  })

  // Menu
  function isDesktop () {
    return $(window).width() > 1000
  }
  function moveScreen (actionRoot, direction) {
    // Make
    var currentShown = actionRoot.find("> div:visible").index()
    var nextToShow = (currentShown  + direction) % 3; 
    actionRoot.find("> div").hide()
    actionRoot.find("> div").eq(nextToShow).show()
    // Ajust the size on desktop
    if (isDesktop()) {
      var otherRoot = $(actionRoot.siblings()[0])
      if (nextToShow === 0) {
        // Reset it
        actionRoot.css("height", "50%")
        otherRoot.css("height", "50%")
        
      } else {
        // Find the other actionroot and minimize it
        otherRoot.css("height", "auto")
        otherRoot.css("height", otherRoot.height() + 20 + "px" )
        var otherHeight = otherRoot.height()
        // Expand the current
        actionRoot.css("height", "calc(100% - " + otherHeight + "px)")
      }
    }
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
    $(this).toggleClass("rotated")
  })

  // Hide the other action when beginning one
  $(".launchAction").on("click", function () {
    var myActionRoot = $(this).parentsUntil(".action").parent()
    $(this).parentsUntil(".verticalSplit2").parent()
      .find(".action").not(myActionRoot).find("> div").hide().eq(0).show()
  })

})