<?php
$list = [1, 3, 5, 7, 9];
$sum = 0;
foreach ($list as $number) {
  $sum += $number;
}
echo json_encode([
  "list" => $list,
  "sum" => $sum
]);