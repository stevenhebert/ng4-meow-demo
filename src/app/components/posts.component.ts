import {Component, OnInit} from "@angular/core";
import {ActivatedRoute, Params} from "@angular/router";
import {Observable} from "rxjs";
import {PostService} from "../services/post-service";
import {Post} from "../classes/post";
import construct = Reflect.construct;

@Component({
	templateUrl: "./templates/posts.php"
})

export class PostsComponent {}

posts : Post[] = [];

constructor(protected postService : post-service)