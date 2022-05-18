<?php

function generate_md5_len11() {
    return substr(md5(rand()), 0, 11);
}
