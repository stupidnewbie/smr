<?php
$this->includeTemplate('includes/CommonNews.inc');

if(isset($NewsItems) && count($NewsItems) > 0) { ?>
	<div align="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div>
	<table class="standard">
		<tr>
			<th class="center">Time</th>
			<th class="center">News</th>
		</tr>
		<?php
		foreach($NewsItems as $NewsItem) { ?>
			<tr>
				<td class="center"><?php echo date(DATE_FULL_SHORT, $NewsItem['Time']); ?></td>
				<td><?php echo $NewsItem['Message']; ?></td>
			</tr><?php
		} ?>
		</table><?php
}
else {
	?>You have no current news.<?php
} ?>
