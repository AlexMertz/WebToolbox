$(document).ready(function() {

  // Do not save any inputs, fix FF bug
  $("input[checked='checked']").each(function() {
    $(this).prop('checked', true)
  })

  // Calendar
  $('#calendar').fullCalendar({
    hiddenDays: [ 0 ],
    locale: "fr",
    allDaySlot: false,
    slotLabelFormat: "HH:mm",
    buttonText: { today: "Aujourd'hui" },
    defaultView: "agendaWeek",
    height: "parent",
    slotDuration : '00:15:00',
    displayEventTime: false,
    viewRender: function( view, element ) {
      var date = view.start.format('YYYY-w')
      $.ajax({
        method: "POST",
        url: "api?action=getSchedule",
        dataType: "json",
        data : {
          "login" : window.user,
        }
      }).done(function(data) {
          renderEvents("Conducteur", data.asDriver, "#448ccb")
          renderEvents("Passager", data.asPassenger, "#77bdf8")
      })
    },
    eventClick: function(calEvent, jsEvent, view) {
      /*
      alert('Event: ' + calEvent.title);
      alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
      alert('View: ' + view.name);
      // change the border color just for fun
      $(this).css('border-color', 'red');
      */
      // Fill the modal
      var m = $('#myModal')
      m.find('.modal-title').text()
      m.modal("show")
    }
  })

  function renderEvents (name, data, color) {
    for (trip of data) {
      for (date of trip.dates.split(",")) {
        var time = trip.time.split(" ➞ ")
        for (var index = 0; index < time.length; index++) {
          if (time[index].length === 5)
            time[index] = moment(date + time[index], "YYYY-MM-DDHH[h]mm")
          else if (time[index].length === 4)
            time[index] = moment(date + time[index], "YYYY-MM-DDH[h]mm")
          else if (time[index].length === 2)
            time[index] = moment(date + time[index], "YYYY-MM-DDH[h]")
          else
            alert("Date parse error")
        }
        var start = time[0]
        var end = time[1]
        $("#calendar").fullCalendar( 'renderEvent', { 
          "title": name, 
          "start" : start,
          "end" : end,
          "color" : color,
          "link": null
        })
      }
    }
  }

  $('#myModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    console.log(event)
    var hello = button.data('hello') // Extract info from data-* attributes
    var modal = $(this)
    modal.find('.modal-title').text(hello)
    //modal.find('.modal-body input').val(recipient)
  })

  // Menu
  function isDesktop () {
    return $(window).width() > 1000
  }
  function moveScreen (actionRoot, direction) {
    // Make
    window.a = actionRoot
    var currentShown = actionRoot.find("> div:visible").index()
    var total = actionRoot.find("> div").length;
    var nextToShow = (currentShown  + direction) % total; 
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
  $('input:radio[name="recurrency"]').change(function() {
    if (this.checked && this.value == 'ponctual') {
      $('.ponctual').show()
      $(this).parentsUntil("form").parent().find(".dayOfTheWeek").hide()
    } else {
      $('.ponctual').hide()
      $(this).parentsUntil("form").parent().find(".dayOfTheWeek").show()
    }
  }) 

  // Launch the arrow animation
  $(".changeDirection").on("click", function () {
    $(this).toggleClass("rotated")
    // Toggle Values
    var input = $(this).siblings("input")
    var oldValue = input.val()
    input.val(input.attr("data-old-value"))
    input.attr("data-old-value", oldValue)
  })

  // Hide the other action when beginning one
  $(".launchAction").on("click", function () {
    var myActionRoot = $(this).parentsUntil(".action").parent()
    $(this).parentsUntil(".verticalSplit2").parent()
      .find(".action").not(myActionRoot).find("> div").hide().eq(0).show()
  })

  $("#submit-propose").on("click", function () {
    var self = $(this)
    var myForm = $(this).closest(".form")
    var data = myForm.serialize()
    var method = myForm.attr("method")
    var target = myForm.attr("action")
    $.ajax({
      url: target,
      method: method,
      data: data,
    }).done(function() {
      self.addClass("btn-success")
      self.html('<i class="fa fa-check" aria-hidden="true"></i> Proposé !')
    }).fail(function(jqXHR, textStatus) {
      self.addClass("btn-danger")
      self.html('<i class="fa fa-frown-o" aria-hidden="true"></i> Erreur !')
      alert( "Erreur : " + jqXHR.responseText );
    }).always(function(jqXHR, textStatus) {
      self.removeClass("btn-primary")
      self.attr("disabled", "disabled")
    });
  })

  $("#submit-search").on("click", function () {
    var self = $(this)
    var myForm = $(this).closest(".form")
    var data = myForm.serialize()
    var recurrency = myForm.find("input[name='recurrency']").val()
    var ponctual = myForm.find("input[name='ponctual']").val()
    var method = myForm.attr("method")
    var target = myForm.attr("action")
    $.ajax({
      url: target,
      method: method,
      data: data,
    }).done(function(jqXHR, textStatus) {
      try {
        var response = JSON.parse(jqXHR)
        if (response.match === "0%")
          alert("Aucun covoiturage disponible à cette heure: (")
        else {
          var myActionRoot = self.parentsUntil(".action").parent()
          moveScreen(myActionRoot, 1)
          var divResults = myActionRoot.find(".results")
          var template = divResults.find(".template")
          var listResults = divResults.find(".listResults")
          listResults.html("")
          response.proposals.forEach(function (p) {
            var t = $(template.html())
            t.find("img").attr("src", "https://demeter.utc.fr/portal/pls/portal30/portal30.get_photo_utilisateur?username=" + p.driver)
            t.find(".driver").text(p.firstName + " " + p.lastName)
            t.find(".direction").text(p.direction)
            var d = [ "Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Dimanche"]
            t.find(".date").text(d[p.dayOfTheWeek])
            t.find(".time").text(p.time)
            t.find(".nbPassengers").text(p.nbPassengers)
            t.find(".nbSeats").text(p.nbSeats)
            t.find(".passengers").text(p.passengers)
            t.find(".book").attr("data-recurrency", recurrency)
            t.find(".book").attr("data-ponctual", ponctual)
            listResults.append(t)
          })
        }
      } catch (e) {
        alert(jqXHR)
      }
    }).fail(function(jqXHR, textStatus) {
      alert( "Erreur : " + jqXHR.responseText );
    })
  })

  $(".results").on("click", ".book", function () {
      var recurrency = $(this).attr("data-recurrency")
      var id = $(this).attr("data-id")
      var ponctual = $(this).attr("data-ponctual")
      $.ajax({
        url: "api.php?action=book",
        method: "POST",
        data: { 
          recurrency: recurrency,
          ponctual: ponctual,
          id: id
      },
      }).done(function(jqXHR, textStatus) {
          alert("ok")
      }).fail(function(jqXHR, textStatus) {
        alert( "Erreur : " + jqXHR.responseText );
      })
  })

  $("form input, form select, .ponctualInput").on("change", function () {
    var button = $(this).closest("form").find(".submit")
    button.text("Proposer")
    button.removeAttr("disabled")
    button.removeClass("btn-success btn-danger").addClass("btn-primary")
  })

  $(".action").on("click", function (e) {
    if (e.target !== this) // Only this el, not children
      return
    $(this).find("div.mainActionButton:visible").trigger( "click" )
  })




})