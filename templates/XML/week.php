<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<logickehry>
    <game_types><?php foreach($game_type as $gt) { ?>
        <game_type id="<?= $gt->game_type_id ?>" subscribees="<?php $gt->subscribed_users ?>">
            <name><?= $gt->game_name ?></name>
            <average_playtime><?= $gt->averate_playtime ?></average_playtime>
            <players>
                <min><?= $gt->min_players ?></min>
                <max><?= $gt->max_players ?></max>
            </players>
            <rating>
                <average><?= $gt->average_score ?></average>
                <count><?= $gt->rating_count ?></count>
            </rating>
        </game_type>
    <?php } ?></game_types>
    <game_boxes><?php foreach($game_box as $gb){ ?>
        <gamebox id="<?= $gb->game_box-id ?>">
            <game_type><?= $gb->game_box_id ?></game_type>
            <track_code><?= $gb->tracking_code ?></track_code>
            <note><?= $gb->additional_note ?></note>
        </gamebox>
    <?php } ?></game_boxes>
    <reservations><?php foreach($reservation as $res){ ?>
        <reservation id="<?= $res->reservation_id?>" open="<?= $res->open_reservation ?>">
            <reservee><?= $res->orion_login ?></reservee>
            <game_box_id><?= $res->game_box_id ?></game_box_id>
            <time_from><?= $res->time_from ?></time_from>
            <time_to><?= $res->time_to ?></time_to>
            <players>
                <min><?= $res->min_players ?></min>
                <cur><?= $res->signed ?></cur>
                <max><?= $res->max_players ?></max>
            </players>
        </reservation>
    <?php } ?></reservations>
</logickehry>