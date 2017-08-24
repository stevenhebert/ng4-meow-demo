<?php

namespace Edu\Cnm\Ng4Demo\Test;

use Edu\Cnm\CreepyOctoMeow\Test\Ng4DemoTest;
use Edu\Cnm\Ng4Demo\Post;

//grab the project test parameters
require_once ("Ng4DemoTest.php");

//grab the classes under scrutiny
require_once (dirname(__DIR__) . "/autoload.php");

/**
 * Full PHPUnit test for the Post class
 *
 * This is a complete PHPUnit test of the Post class. It is complete because *ALL* mySQL/PDO enabled methods
 * are tested for both invalid and valid inputs.
 *
 * @see Post
 * @author Rochelle Lewis <rlewis37@cnm.edu>
 **/
class PostTest extends Ng4DemoTest {
	/**
	 * content of the Post
	 * @var string $VALID_CONTENT
	 **/
	protected $VALID_CONTENT = "This is a valid post!";

	/**
	 * content of the Post to test update method
	 * @var string $VALID_CONTENT_2
	 **/
	protected $VALID_CONTENT_2 = "This is an updated post! Yay!";

	/**
	 * date of the Post
	 * @var \DateTime $VALID_DATE
	 **/
	protected $VALID_DATE = null;

	/**
	 * beginning date to test post date range search
	 * @var \DateTime $SUNRISE_DATE
	 **/
	protected $SUNRISE_DATE = null;

	/**
	 * end date to test post date range search
	 * @var \DateTime $SUNSET_DATE
	 **/
	protected $SUNSET_DATE = null;

	/**
	 * title of the Post
	 * @var string $VALID_TITLE
	 **/
	protected $VALID_TITLE = "I'm a valid post title!";

	/**
	 * create dependent objects before running each test
	 **/
	public final function setUp() {
		//run the default setUp() method first
		parent::setUp();

		//create a valid post date - this gives us something pre-set to check against
		$this->VALID_DATE = new \DateTime();

		//create a valid SUNRISE date for date range check
		$this->SUNRISE_DATE = new \DateTime();
		$this->SUNRISE_DATE->sub(new \DateInterval("P10D"));

		//create a valid SUNSET date for date range check
		$this->SUNSET_DATE = new \DateTime();
		$this->SUNSET_DATE->add(new \DateInterval("P10D"));
	}

	/**
	 * test inserting a valid Post and verify that the actual mySQL data matches
	 **/
	public function testInsertValidPost() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//grab the post back from mysql and check if all fields match
		$pdoPost = Post::getPostByPostId($this->getPDO(), $post->getPostId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertEquals($pdoPost->getPostContent(), $this->VALID_CONTENT);
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
		$this->assertEquals($pdoPost->getPostTitle(), $this->VALID_TITLE);
	}

	/**
	 * test inserting a Post that already exists
	 *
	 * @expectedException \PDOException
	 **/
	public function testInsertInvalidPost() {
		//create a post with a non-null post id and watch it fail
		$post = new Post(Ng4DemoTest::INVALID_KEY, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());
	}

	/**
	 * test inserting a Post, editing it, and then updating it
	 **/
	public function testUpdateValidPost() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//edit the post and run update method
		$post->setPostContent($this->VALID_CONTENT_2);
		$post->update($this->getPDO());

		//grab the post back from mysql and check if all fields match
		$pdoPost = Post::getPostByPostId($this->getPDO(), $post->getPostId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertEquals($pdoPost->getPostContent(), $this->VALID_CONTENT_2);
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
		$this->assertEquals($pdoPost->getPostTitle(), $this->VALID_TITLE);
	}

	/**
	 * test updating a Post that does not exist
	 *
	 * @expectedException \PDOException
	 **/
	public function testUpdateInvalidPost() {
		//create a post, don't insert it, try updating it and watch it fail
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->update($this->getPDO());
	}

	/**
	 * test creating a Post and then deleting it
	 **/
	public function testDeleteValidPost() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//verify the row has been inserted, then run delete
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$post->delete($this->getPDO());

		//try and grab it back from mysql and verify that you get nothing
		$pdoPost = Post::getPostByPostId($this->getPDO(), $post->getPostId());
		$this->assertNull($pdoPost);
		$this->assertEquals($numRows, $this->getConnection()->getRowCount("post"));
	}

	/**
	 * test deleting a Post that does not exist
	 *
	 * @expectedException \PDOException
	 **/
	public function testDeleteInvalidPost() {
		//create a post, don't insert it, try deleting it and watch it fail
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->delete($this->getPDO());
	}

	/**
	 * test grabbing Posts by post content
	 **/
	public function testGetValidPostsByPostContent() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//grab the posts from mysql, verify row count and namespace is correct
		$results = Post::getPostsByPostContent($this->getPDO(), $this->VALID_CONTENT);
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\Ng4Demo\\Post", $results);

		//verify that all fields match
		$pdoPost = $results[0];
		$this->assertEquals($pdoPost->getPostId(), $post->getPostId());
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
		$this->assertEquals($pdoPost->getPostTitle(), $this->VALID_TITLE);
	}

	/**
	 * test grabbing Posts by content that does not exist
	 **/
	public function testGetPostsByInvalidPostContent() {
		$posts = Post::getPostsByPostContent($this->getPDO(), "you will find nothing");
		$this->assertCount(0, $posts);
	}

	/**
	 * test grabbing Posts by post date range
	 **/
	public function testGetValidPostsByPostDateRange() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//grab the posts from mysql, verify row count and namespace is correct
		$results = Post::getPostsByPostDateRange($this->getPDO(), $this->SUNRISE_DATE, $this->SUNSET_DATE);
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\Ng4Demo\\Post", $results);

		//verify that all fields match
		$pdoPost = $results[0];
		$this->assertEquals($pdoPost->getPostId(), $post->getPostId());
		$this->assertEquals($pdoPost->getPostContent(), $this->VALID_CONTENT);
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
		$this->assertEquals($pdoPost->getPostTitle(), $this->VALID_TITLE);
	}

	/**
	 * test grabbing Posts by a date that does not exist
	 **/
	public function testGetPostsByInvalidDateRange() {
		$posts = Post::getPostsByPostDateRange($this->getPDO(), $this->SUNRISE_DATE, $this->SUNSET_DATE);
		$this->assertCount(0, $posts);
	}

	/**
	 * test grabbing Posts by title
	 **/
	public function testGetValidPostsByPostTitle() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//grab the posts from mysql, verify row count and namespace is correct
		$results = Post::getPostsByPostTitle($this->getPDO(), $this->VALID_TITLE);
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\Ng4Demo\\Post", $results);

		//verify that all fields match
		$pdoPost = $results[0];
		$this->assertEquals($pdoPost->getPostId(), $post->getPostId());
		$this->assertEquals($pdoPost->getPostContent(), $this->VALID_CONTENT);
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
	}

	/**
	 * test grabbing Posts by a title that does not exist
	 **/
	public function testGetPostsByInvalidPostTitle() {
		$posts = Post::getPostsByPostTitle($this->getPDO(), "you will find nothing");
		$this->assertCount(0, $posts);
	}

	/**
	 * test grabbing all Posts
	 **/
	public function testGetAllValidPosts() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("post");

		//create a new post and insert
		$post = new Post(null, $this->VALID_CONTENT, $this->VALID_DATE, $this->VALID_TITLE);
		$post->insert($this->getPDO());

		//grab the posts from mysql, verify row count and namespace is correct
		$results = Post::getAllPosts($this->getPDO());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("post"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\Ng4Demo\\Post", $results);

		//verify that all fields match
		$pdoPost = $results[0];
		$this->assertEquals($pdoPost->getPostId(), $post->getPostId());
		$this->assertEquals($pdoPost->getPostContent(), $this->VALID_CONTENT);
		$this->assertEquals($pdoPost->getPostDate(), $this->VALID_DATE);
		$this->assertEquals($pdoPost->getPostTitle(), $this->VALID_TITLE);
	}
}