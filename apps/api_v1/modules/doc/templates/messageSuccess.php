<h1>message</h1>

<h2>create</h2>
<blockquote><?php echo link_to(url_for('message/create', true), 'message/create') ?> (POST)</blockquote> 

<h2>delete</h2>
<blockquote><?php echo link_to(url_for('message/delete', true), 'message/delete') ?> (DELETE)</blockquote> 

<h2>list</h2>
<blockquote><?php echo link_to(url_for('message/index', true), 'message/index') ?> (GET)</blockquote> 

<h2>show</h2>
<blockquote><?php echo link_to(url_for('message/show', true), 'message/show') ?> (GET)</blockquote> 

<h2>update</h2>
<blockquote><?php echo link_to(url_for('message/update', true), 'message/update') ?> (PUT)</blockquote> 