<h1>subredditmembership</h1>

<h2>create</h2>
<blockquote><?php echo link_to(url_for('subredditmembership/create', true), 'subredditmembership/create') ?> (POST)</blockquote> 

<h2>delete</h2>
<blockquote><?php echo link_to(url_for('subredditmembership/delete', true).'/[id]', 'subredditmembership/delete') ?> (DELETE)</blockquote> 

<h2>list</h2>
<blockquote><?php echo link_to(url_for('subredditmembership/index', true), 'subredditmembership/index') ?> (GET)</blockquote> 

<h2>show</h2>
<blockquote><?php echo link_to(url_for('subredditmembership/show', true).'/[id]', 'subredditmembership/show') ?> (GET)</blockquote> 

<h2>update</h2>
<blockquote><?php echo link_to(url_for('subredditmembership/update', true).'/[id]', 'subredditmembership/update') ?> (PUT)</blockquote> 