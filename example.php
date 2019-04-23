<?php

set_time_limit(0);

require __DIR__.'/KanoonDownloader.php';

echo KanoonDownloader::downloadQuestions('13980130', '1');
