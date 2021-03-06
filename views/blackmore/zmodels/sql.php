<?php views::viewFile('{core}views/blackmore/header.php'); ?>
				<table id="pageMiddleTable">
					<tr>
						<td id="pageMiddleSidebar">
							<div class="middleLine">
								<?php if(isset($error)) { ?>
									<div class="message errormsg">
										<p><?php echo $error; ?></p>
									</div>
								<?php } ?>

								<?php if(session::existsFlash('notification')) { ?>
									<div class="message info">
										<p><?php echo session::getFlash('notification'); ?></p>
									</div>
								<?php } ?>

								<div class="menuDivContainer">
<?php views::viewFile('{core}views/blackmore/sectionMenu.php', blackmore::$module); ?>
								</div>
							</div>
							<div class="clear"></div>
						</td>
						<td id="pageMiddleSidebarToggle">
							&laquo;
						</td>
						<td id="pageMiddleContent">
							<div class="topLine"></div>
							<div class="middleLine">

								<h2 class="iconxdashboard"><?php echo _('Dashboard'); ?></h2>

								<table class="fullWidth">
									<tbody>
										<tr>
											<td class="halfWidth">
												<h3><?php echo _('SQL Query:'); ?></h3>
												<div id="placeholder">

												<textarea rows="20" cols="100"><?php echo $sql; ?></textarea>

												</div>
												<div class="clear"></div>
											</td>
											<td class="halfWidth">
												<h3><?php echo _('Statistics:'); ?></h3>
												<div id="placeholderVisitors"></div>
												<div class="clear"></div>
											</td>
										</tr>
									</tbody>
								</table>

							</div>
							<div class="bottomLine"></div>
							<div class="clear"></div>
						</td>
						<td id="pageMiddleExtra">
						</td>
					</tr>
				</table>
<?php views::viewFile('{core}views/blackmore/footer.php'); ?>