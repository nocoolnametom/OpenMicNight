<h1>user</h1>

<h2>create</h2>
<blockquote><?php echo link_to(url_for('user/create', true), 'user/create') ?> (POST)</blockquote> 

<h2>delete</h2>
<blockquote><?php echo link_to(url_for('user/delete', true), 'user/delete') ?> (DELETE)</blockquote> 

<h2>list</h2>
<blockquote><?php echo link_to(url_for('user/index', true), 'user/index') ?> (GET)</blockquote> 

<h2>show</h2>
<blockquote><?php echo link_to(url_for('user/show', true), 'user/show') ?> (GET)</blockquote> 

<h2>update</h2>
<blockquote><?php echo link_to(url_for('user/update', true), 'user/update') ?> (PUT)</blockquote>

<h2>token</h2>
<blockquote><?php echo link_to(url_for('user/token', true), 'user/token') ?> (PUT)</blockquote>