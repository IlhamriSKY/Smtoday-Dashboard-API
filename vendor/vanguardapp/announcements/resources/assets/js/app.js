$("#announcements-icon").click(function () {
    $.post("/announcements/read");
    $(".announcements .activity-badge").remove();
});
