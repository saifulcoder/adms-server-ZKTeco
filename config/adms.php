<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TransFlag
    |--------------------------------------------------------------------------
    |
    | Mask returned in the handshake that tells the terminal which record
    | types to push to the server on its own. Position by position, as defined
    | by the ZKTeco attendance PUSH protocol:
    |
    |    1 AttLog      2 OpLog      3 AttPhoto    4 EnrollFP
    |    5 EnrollUser  6 FPImag     7 ChgUser     8 ChgFP
    |    9 FACE       10 UserPic   11 WORKCODE   12 BioPhoto
    |
    | Positions 4 and 8 make a terminal upload a fingerprint template as soon
    | as it is enrolled or changed. Position 9 does the same for face
    | templates, and position 12 for the comparison photo that visible-light
    | terminals (SpeedFace etc.) keep as the face enrolment. Without those
    | bits the only way to get the data is to ask for it explicitly.
    |
    | The default turns on everything except user pictures (10) and work
    | codes (11), which nothing here consumes. It was 1111111000 before, which
    | left ChgFP, FACE and BioPhoto off - so no face data ever arrived.
    |
    | Firmware that only understands the named form can be given that instead,
    | e.g. "TransData AttLog\tOpLog\tEnrollUser\tChgUser\tEnrollFP\tChgFP\tFACE\tBioPhoto".
    |
    */
    'trans_flag' => env('ADMS_TRANS_FLAG', '111111111001'),

    /*
    |--------------------------------------------------------------------------
    | Push protocol negotiation (opt-in)
    |--------------------------------------------------------------------------
    |
    | Terminals speak the lower of their own pushver and the PushProtVer the
    | server advertises; when the server says nothing they assume the oldest
    | protocol. Unified templates (BIODATA) and comparison photos (BIOPHOTO) -
    | the only face data a visible-light terminal has - only exist in the newer
    | protocol, so such a terminal may send no face data until it is told the
    | server speaks 2.4.1.
    |
    | This changes how the terminal talks to the server in general, so it is
    | off by default. Set ADMS_PUSH_PROT_VER=2.4.1 to turn it on, and list
    | serial numbers in ADMS_PUSH_PROT_VER_DEVICES to try it on a few terminals
    | first (empty = every terminal).
    |
    | multi_bio_*_support are the per-type capability masks sent with it,
    | indexed by biometric type: 0 general, 1 fingerprint, 2 near-infrared
    | face, ... 9 visible-light face.
    |
    */
    'push_prot_ver' => env('ADMS_PUSH_PROT_VER'),

    'push_prot_ver_devices' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMS_PUSH_PROT_VER_DEVICES', ''))
    ))),

    'multi_bio_data_support' => env('ADMS_MULTI_BIO_DATA_SUPPORT', '0:1:1:0:0:0:0:0:0:1'),

    'multi_bio_photo_support' => env('ADMS_MULTI_BIO_PHOTO_SUPPORT', '0:0:0:0:0:0:0:0:0:1'),

    /*
    |--------------------------------------------------------------------------
    | Fingerprint pull
    |--------------------------------------------------------------------------
    |
    | finger_ids  Finger slots queried when pulling templates for an employee.
    |             A terminal stores up to ten (0-9); querying all ten costs ten
    |             commands per employee, so narrow this if your enrolment
    |             policy only uses a couple of fingers.
    |
    | query_userinfo  Also queue a DATA QUERY USERINFO alongside a bulk pull,
    |             so the user record arrives with the templates.
    |
    */
    'finger_ids' => array_map(
        'intval',
        explode(',', (string) env('ADMS_FINGER_IDS', '0,1,2,3,4,5,6,7,8,9'))
    ),

    'query_userinfo' => (bool) env('ADMS_QUERY_USERINFO', true),

    /*
    |--------------------------------------------------------------------------
    | Face pull
    |--------------------------------------------------------------------------
    |
    | pull_faces  Also ask for face templates whenever fingerprints are pulled.
    |
    | face_types  Biometric types queried (DATA QUERY BIODATA Type=n):
    |             2 = near-infrared face, 9 = visible-light face. A terminal
    |             that does not have a given type just reports the command as
    |             failed, so asking for both is harmless.
    |
    */
    'pull_faces' => (bool) env('ADMS_PULL_FACES', true),

    'face_types' => array_map(
        'intval',
        explode(',', (string) env('ADMS_FACE_TYPES', '2,9'))
    ),

    /*
    | Guard against queueing thousands of commands in a single click.
    */
    'max_pull_commands' => (int) env('ADMS_MAX_PULL_COMMANDS', 2000),

    /*
    | How many queued commands to hand a terminal in one /iclock/getrequest
    | response. Terminals poll every few seconds, so a small batch drains a
    | large pull quickly without sending a multi-megabyte reply. 0 = no limit.
    */
    'commands_per_request' => (int) env('ADMS_COMMANDS_PER_REQUEST', 20),

    /*
    | A device counts as online if it contacted the server within this many
    | minutes (same threshold devices:check-status alerts on).
    */
    'online_threshold_minutes' => (int) env('ADMS_ONLINE_THRESHOLD_MINUTES', 3),

];
