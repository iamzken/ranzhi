<?php
if(!isset($lang->leave)) $lang->leave = new stdclass();
$lang->leave->common = 'Leave';
$lang->leave->browse = 'Browse leave';
$lang->leave->create = 'Create';
$lang->leave->edit   = 'Edit';
$lang->leave->delete = 'Delete';
$lang->leave->review = 'Review';
$lang->leave->cancel = 'Cancel';
$lang->leave->commit = 'Commit';
$lang->leave->export = 'Export';

$lang->leave->personal     = 'My leave';
$lang->leave->browseReview = 'Review list';
$lang->leave->company      = 'All leave';

$lang->leave->id           = 'ID';
$lang->leave->year         = 'Year';
$lang->leave->begin        = 'Begin date';
$lang->leave->end          = 'End date';
$lang->leave->start        = 'Begin time';
$lang->leave->finish       = 'End time';
$lang->leave->hours        = 'Hours';
$lang->leave->type         = 'Type';
$lang->leave->desc         = 'Description';
$lang->leave->status       = 'Status';
$lang->leave->createdBy    = 'Created by';
$lang->leave->createdDate  = 'Created date';
$lang->leave->reviewedBy   = 'Reviewed by';
$lang->leave->reviewedDate = 'Reviewed date';
$lang->leave->date         = 'Date';
$lang->leave->time         = 'Time';

$lang->leave->typeList['affairs']   = 'Affairs';
$lang->leave->typeList['sick']      = 'Sick';
$lang->leave->typeList['annual']    = 'Annual';
$lang->leave->typeList['lieu']      = 'Lieu';
$lang->leave->typeList['home']      = 'Home';
$lang->leave->typeList['marry']     = 'Marry';
$lang->leave->typeList['maternity'] = 'Maternity';

$lang->leave->paid   = 'Paid Leave';
$lang->leave->unpaid = 'Unpaid Leave';

$lang->leave->statusList['draft']  = 'Draft';
$lang->leave->statusList['wait']   = 'Wait';
$lang->leave->statusList['pass']   = 'Pass';
$lang->leave->statusList['reject'] = 'Reject';

$lang->leave->denied = 'access denied';
$lang->leave->unique = 'There was a record of leave in %s.';

$lang->leave->confirmReview['pass']   = 'Are you sure to pass it?';
$lang->leave->confirmReview['reject'] = 'Are you sure to reject it?';

$lang->leave->hoursTip = 'Hour';
