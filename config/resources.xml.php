<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<resources>
		<directoryList>
			<directory>
				<name>scabbia/</name>
				<path>{core}resources/</path>
			</directory>
			<scope mode="development">
				<directory>
					<name>docs/</name>
					<path>{core}resources/docs/</path>
					<autoViewer>
						<defaultPage>index.md</defaultPage>
						<viewEngine>viewengine_markdown</viewEngine>
						<header>{core}views/docs/header.php</header>
						<footer>{core}views/docs/footer.php</footer>
					</autoViewer>
				</directory>
			</scope>
		</directoryList>
		<fileList />
		<packList>
			<pack>
				<name>scabbia.js</name>
				<type>js</type>
				<partList>
					<part>
						<type>file</type>
						<path>{core}resources/laroux/laroux.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jquery</class>
						<path>{core}resources/jquery/sizzle.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jquery</class>
						<path>{core}resources/jquery/jquery-1.8.1-custom.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jquery</class>
						<path>{core}resources/jquery/jquery.fix.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.core.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.widget.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.mouse.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.position.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.sortable.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.tabs.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.autocomplete.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.datepicker.js</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/laroux/laroux.ui.js</path>
					</part>
					<part>
						<type>file</type>
						<class>map</class>
						<path>{core}resources/mapbox/mapbox-0.6.5.js</path>
					</part>
					<part>
						<type>file</type>
						<class>cleditor</class>
						<path>{core}resources/cleditor/jquery.cleditor.js</path>
					</part>
					<part>
						<type>file</type>
						<class>cleditor</class>
						<path>{core}resources/cleditor/jquery.cleditor.xhtml.js</path>
					</part>
					<part>
						<type>file</type>
						<class>shadowbox</class>
						<path>{core}resources/shadowbox/shadowbox.js</path>
					</part>
					<part>
						<type>file</type>
						<class>tablesorter</class>
						<path>{core}resources/tablesorter/jquery.tablesorter.js</path>
					</part>
					<part>
						<type>file</type>
						<class>tablesorter</class>
						<path>{core}resources/tablesorter/jquery.tablesorter.pager.js</path>
					</part>
					<part>
						<type>file</type>
						<class>snippet</class>
						<path>{core}resources/snippet/jquery.snippet.js</path>
					</part>
					<part>
						<type>file</type>
						<class>flot</class>
						<path>{core}resources/flot/jquery.flot.js</path>
					</part>
					<part>
						<type>file</type>
						<class>flot</class>
						<path>{core}resources/flot/jquery.flot.pie.js</path>
					</part>
					<scope mode="development">
						<part>
							<type>file</type>
							<class>blackmore</class>
							<path>{core}resources/blackmore/blackmore.js</path>
						</part>
					</scope>
					<part>
						<type>function</type>
						<name>mvc::exportAjaxJs</name>
					</part>
				</partList>
			</pack>
			<pack>
				<name>scabbia.css</name>
				<type>css</type>
				<partList>
					<part>
						<type>file</type>
						<class>reset</class>
						<path>{core}resources/reset/reset.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.core.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.autocomplete.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.tabs.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.datepicker.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/jquery/jquery.ui.theme.css</path>
					</part>
					<part>
						<type>file</type>
						<class>jqueryui</class>
						<path>{core}resources/laroux/laroux.ui.css</path>
					</part>
					<part>
						<type>file</type>
						<class>map</class>
						<path>{core}resources/mapbox/mapbox.0.6.5.css</path>
					</part>
					<part>
						<type>file</type>
						<class>cleditor</class>
						<path>{core}resources/cleditor/jquery.cleditor.css</path>
					</part>
					<part>
						<type>file</type>
						<class>shadowbox</class>
						<path>{core}resources/shadowbox/shadowbox.css</path>
					</part>
					<part>
						<type>file</type>
						<class>tablesorter</class>
						<path>{core}resources/tablesorter/style.css</path>
					</part>
					<part>
						<type>file</type>
						<class>snippet</class>
						<path>{core}resources/snippet/jquery.snippet.css</path>
					</part>
					<scope mode="development">
						<part>
							<type>file</type>
							<class>blackmore</class>
							<path>{core}resources/blackmore/blackmore.css</path>
						</part>
						<part>
							<type>file</type>
							<class>docs</class>
							<path>{core}resources/docs/markdown.css</path>
						</part>
					</scope>
				</partList>
			</pack>
		</packList>
	</resources>
</scabbia>