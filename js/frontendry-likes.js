(function ($) {
  ("use strict");

  //####### on page load, retrive votes for each content
  $.each($(".voting_wrapper"), function () {
    let $this = $(this),
      unique_id = $(this).attr("data-id");

    //prepare post content
    let ajaxData = {
      action: "frontendry_likes_processor",
      unique_id: unique_id,
      vote: "fetch",
    };

    //post_data = { unique_id: unique_id, vote: "fetch" };

    //send our data to "vote_process.php" using jQuery $.post()
    $.post(
      frontendry_likes_object.ajaxurl,
      ajaxData,
      function (response) {
        //retrive votes from server, replace each vote count text
        $this.find(".up_votes").text(response.vote_up);
        $this.find(".down_votes").text(response.vote_down);
      },
      "json"
    );
  });

  $(document).on("click", ".voting_btn", function (e) {
    let $thisBtn = $(this),
      //get class name (down_button / up_button) of clicked element
      clicked_button = $thisBtn.children().attr("class");

    //get unique ID from voted parent element
    let unique_id = $thisBtn.parent().attr("data-id");

    if (clicked_button === "down_button") {
      //user disliked the content
      //prepare post content
      let ajaxData = {
        action: "frontendry_likes_processor",
        unique_id: unique_id,
        vote: "down",
      };

      //send our data to "vote_process.php" using jQuery $.post()
      $.post(frontendry_likes_object.ajaxurl, ajaxData, function (data) {
        //replace vote down count text with new values
        $thisBtn.closest(".voting_btn").find(".down_votes").text(data);

        //thank user for the dislike
        alert("Thanks! Each Vote Counts, Even Dislikes!");
      }).fail(function (err) {
        //alert user about the HTTP server error
        alert(err.statusText);
      });
    } else if (clicked_button === "up_button") {
      //user liked the content
      //prepare post content

      var ajaxData = {
        action: "frontendry_likes_processor",
        unique_id: unique_id,
        vote: "up",
      };
      //post_data = { unique_id: unique_id, vote: "up" };

      //send our data to "vote_process.php" using jQuery $.post()
      $.post(frontendry_likes_object.ajaxurl, ajaxData, function (data) {
        console.log(data);
        //replace vote up count text with new values
        $thisBtn.closest(".voting_btn").find(".up_votes").text(data);

        //thank user for liking the content
        alert("Thanks! For Liking This Content.");
      }).fail(function (err) {
        //alert user about the HTTP server error
        alert(err.statusText);
      });
    }
  });
})(jQuery);
