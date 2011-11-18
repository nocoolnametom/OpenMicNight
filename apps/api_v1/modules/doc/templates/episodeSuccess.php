<h1>episode</h1>

<h2>create</h2>
<blockquote><?php echo link_to(url_for('episode/create', true), 'episode/create') ?> (POST)</blockquote> 

<h2>delete</h2>
<blockquote><?php echo link_to(url_for('episode/delete', true).'/[id]', 'episode/delete') ?> (DELETE)</blockquote> 

<h2>list</h2>
<blockquote><?php echo link_to(url_for('episode/index', true), 'episode/index') ?> (GET)</blockquote> 

<h2>show</h2>
<blockquote><?php echo link_to(url_for('episode/show', true).'/[id]', 'episode/show') ?> (GET)</blockquote> 

<h2>update</h2>
<blockquote><?php echo link_to(url_for('episode/update', true).'/[id]', 'episode/update') ?> (PUT)</blockquote> 