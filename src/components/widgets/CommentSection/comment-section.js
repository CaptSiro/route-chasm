class WCommentSection extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef CommentSectionJSONType
     * @property {boolean} areCommentsAvailable
     * @property {boolean} webpageID
     * @property {boolean} creatorID
     *
     * @typedef {CommentSectionJSONType & WidgetJSON} CommentSectionJSON
     */

    #commentContainer;

    #topLevelInfiniteLoader;

    #commentCount;

    #json;
    get json() {
        return this.#json;
    }

    /**
     * @param {CommentSectionJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        const commentRoot = jsml.div("comment-root");
        super(jsml.section("w-comment-section", commentRoot), parent, editable);

        this.removeMargin();
        this.childSupport = this.childSupport;
        this.commentRoot = commentRoot;
        this.#json = json;
        const root = this.getRoot();

        if (!json.areCommentsAvailable) {
            this.rootElement.classList.add("display-none");
        }

        root.addJSONListener?.call(root, json => {
            this.rootElement.classList.toggle("display-none", !json.areCommentsAvailable);
        });

        this.#commentContainer = jsml.div("comments-container");

        let commentForm = undefined;
        if (user !== null) {
            commentForm = CommentForm(this, undefined, editable);
        }


        this.commentRoot.append(
            Div("comments-count", [
                "Comments: ",
                Async(async () => {
                    this.#commentCount = Span(__, Number(await AJAX.get("/comments/count/" + json.webpageID, TextHandler(), {}, AJAX.SERVER_HOME)).toLocaleString());
                    return this.#commentCount;
                }, Span(__, "..."))
            ]),
            commentForm ?? Div("login-error", "You need to be logged to write comments."),
            this.#commentContainer
        );

        if (editable === true) {
            this.#commentContainer.append(
                Comment({
                    content: `[[["THIS COMMENT SECTION IS JUST FOR PREVIEW.", 0]]]`,
                    dateAdded: Date.now(),
                    isTopLevel: true,
                    reactionCount: 17,
                    ID: 0,
                    username: "Username",
                    usersID: 0,
                    timePosted: Date.now() - (1000 * 60 * 60 * 24 * 366),
                    creatorID: 9,
                    level: 1,
                    isPinned: true,
                    reaction: 0
                }, this, true),
                Comment({
                    content: `[["Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Fusce suscipit libero eget elit. Nunc auctor. Pellentesque ipsum. Integer vulputate sem a nibh rutrum consequat. In dapibus augue non sapien. Nullam at arcu a est sollicitudin euismod. Aliquam erat volutpat. Fusce wisi. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum fermentum tortor id mi. Aenean fermentum risus id tortor."]]`,
                    dateAdded: Date.now(),
                    isTopLevel: true,
                    reactionCount: 7013,
                    ID: 0,
                    username: "User_1",
                    usersID: 0,
                    timePosted: Date.now() - (60 * 60 * 24 * 7 * 1000),
                    creatorID: 9,
                    level: 0,
                    isPinned: true,
                    reaction: 0
                }, this, true)
            );
            return;
        }

        this.#topLevelInfiniteLoader = new InfiniteScroller(this.#commentContainer, (this.topLevelCommentLoader).bind(this));
    }

    /**
     * @param {number} index
     */
    async topLevelCommentLoader(index) {
        const comments = await AJAX.get(`/comments/${this.#json.webpageID}/${index}`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

        let element;
        for (const comment of comments) {
            comment.isTopLevel = true;
            const commentElement = Comment(comment, this);
            element = commentElement;

            if (document.getElementById(comment.ID) === null) {
                this.#commentContainer.appendChild(commentElement);
            }
        }

        return element;
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WCommentSection}
     */
    static default(parent, editable = false) {
        return new WCommentSection({}, parent, editable);
    }

    /**
     * @override
     * @param {CommentSectionJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WCommentSection}
     */
    static build(json, parent, editable = false) {
        return new WCommentSection(json, parent, editable);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        return (
            NotInspectorAble()
        );
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WCommentSection"
        };
    }

    /** @override */
    remove() {
        console.error("WCommentSection cannot be removed.");
    }

    isSelectionPropagable() {
        return false;
    }

    isSelectAble() {
        return false;
    }

    /**
     * @param {HTMLElement} commentElement
     */
    handlePinned(commentElement) {
        const isPinned = commentElement.classList.contains("pinned");
        const likes = +commentElement.querySelector(".reaction-count").textContent;

        let before;
        for (const topLevelComment of this.#commentContainer.children) {
            if (topLevelComment === commentElement) continue;

            if (isPinned) {
                if (likes > +topLevelComment.querySelector(".reaction-count").textContent) {
                    if (!topLevelComment.classList.contains("pinned")) {
                        before = topLevelComment;
                        break;
                    }
                }

                if (topLevelComment.classList.contains("pinned")) continue;

                before = topLevelComment;
                break;
            } else {
                if (!(likes > +topLevelComment.querySelector(".reaction-count").textContent)) continue;

                before = topLevelComment;
                break;
            }
        }

        if (before !== undefined) {
            this.#commentContainer.insertBefore(commentElement, before);
        } else {
            commentElement.remove();
            this.#commentContainer.appendChild(commentElement);
        }
    }

    /**
     * @param {HTMLElement} commentElement
     */
    prependTopLevel(commentElement) {
        if (this.#commentContainer.children.length === 0) {
            this.#commentContainer.appendChild(commentElement);
            return;
        }

        this.#commentContainer.insertBefore(commentElement, this.#commentContainer.children[0]);
    }

    /**
     * @param {number} by
     */
    updateCommentCount(by) {
        this.#commentCount.textContent = Number(+this.#commentCount.textContent + by).toLocaleString();
    }
}



widgets.define("WCommentSection", WCommentSection);


/**
 * @typedef DatabaseComment
 * @property {number} ID
 * @property {string} content
 * @property {number} creatorID
 * @property {boolean} isPinned
 * @property {number} reactionCount
 * @property {string} timePosted
 * @property {string} username
 * @property {number} usersID
 * @property {number} level
 * @property {number} reaction
 * @property {number} childrenCount
 *
 * @property {boolean} isTopLevel
 * @property {boolean} isJustForShow
 */
/**
 * @param {DatabaseComment} comment
 * @param {WCommentSection} context
 * @param {boolean} isJustForShow
 */
function Comment(comment, context, isJustForShow = false) {
    comment.reactionCount ||= 0;
    comment.reaction ||= 0;

    const content = WTextEditor.build({
        content: JSON.parse(comment.content)
        // mode: "fancy"
    }, context, false);

    const id = guid(true);
    const messageBox = content.rootElement.querySelector("article");
    messageBox.id = id;

    let expand = true;
    const seeMoreButton = (
        Button("see-more display-none", "(see more)", evt => {
            content.rootElement.classList.toggle("expand", expand);
            evt.target.textContent = expand
                ? "(see less)"
                : "(see more)";

            expand = !expand;
        })
    );

    untilElement("#" + messageBox.id)
        .then(() => {
            if (messageBox.offsetHeight < messageBox.scrollHeight) {
                seeMoreButton.classList.remove("display-none");
                return;
            }

            content.rootElement.classList.add("expand");

            delete messageBox.id;
            freeID(id);
        });

    const yourReply = Div("replies your-reply display-none");
    const replies = Div("replies container display-none");

    const reactionCount = Div("reaction-count", Number(+comment.reactionCount + +comment.reaction).toLocaleString());
    let repliesScroller;

    const start = (
        Div("start", [
            Button("arrow up", Span(__, "∧"), async () => {
                if (user === null) return;
                if (start.dataset.reaction === "1") {
                    await removeReaction();
                    return;
                }

                if (isJustForShow === false) {
                    const response = await AJAX.patch(`/comments/react/${comment.ID}/like`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

                    if (response.error) {
                        alert(response.error);
                        return;
                    }

                    if (response.rowCount !== 1) return;
                }

                start.dataset.reaction = "1";
                recalculate();
            }),
            reactionCount,
            Button("arrow down", Span(__, "∨"), async () => {
                if (user === null) return;

                if (start.dataset.reaction === "-1") {
                    await removeReaction();
                    return;
                }

                if (isJustForShow === false) {
                    const response = await AJAX.patch(`/comments/react/${comment.ID}/dislike`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

                    if (response.error) {
                        alert(response.error);
                        return;
                    }

                    if (response.rowCount !== 1) return;
                }

                start.dataset.reaction = "-1";
                recalculate();
            }),
            ...OptionalComponents(comment.isTopLevel, [
                Div("separator"),
                OptionalComponent(user !== null,
                    Button("reply action", "Reply", evt => {
                        if (user === null || isJustForShow !== false) return;

                        evt.target.closest(".comment").classList.add("replying");
                        yourReply.classList.toggle("display-none", false);
                        if (yourReply.children.length === 0) {
                            yourReply.appendChild(CommentForm(context, comment.ID));
                        }
                    })
                ),
                Button("see-replies action" + ((comment.childrenCount ?? 0) < 1 ? " display-none" : ""), `See replies (${comment.childrenCount ?? 0})`, evt => {
                    if (isJustForShow !== false) return;

                    if (repliesScroller === undefined) {
                        repliesScroller = new InfiniteScroller(replies, subCommentsLoader(comment.ID, context, replies));
                    }

                    const isHidden = replies.classList.contains("display-none");
                    replies.classList.toggle("display-none", !isHidden);
                    evt.target.textContent = !isHidden ? `See replies (${evt.target.dataset.count})` : "Hide replies";
                }, { attributes: { "data-count": comment.childrenCount ?? 0 } })
            ])
        ], { attributes: { "data-reaction": String(comment.reaction) } })
    );
    start.dataset.reaction = String(comment.reaction);


    async function removeReaction() {
        if (isJustForShow === false) {
            const response = await AJAX.patch(`/comments/react/${comment.ID}/none`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

            if (response.error) {
                alert(response.error);
                return;
            }

            if (response.rowCount !== 1) return;
        }

        start.dataset.reaction = "0";
        recalculate();
    }

    function recalculate() {
        reactionCount.textContent = Number(+comment.reactionCount + +start.dataset.reaction).toLocaleString();
    }



    return (
        Div("comment " + (comment.isPinned ? "pinned" : ""), [
            Div("published", [
                Div("profile-picture", [
                    Img(AJAX.SERVER_HOME + "/profile/picture/" + comment.usersID + "/?width=250&height=250&cropAndScale=true", "pfp")
                ]),
                Div("text-content expand-able", [
                    Div("column", [
                        Div("date", formatDate(new Date(comment.timePosted))),
                        Div("username", [
                            Heading(3, __, comment.username),
                            Div("tags", [
                                OptionalComponent(comment.level === 0,
                                    Div("admin", "Admin")
                                ),
                                OptionalComponent(comment.creatorID === comment.usersID,
                                    Div("creator", "Creator")
                                )
                            ])
                        ])
                    ]),
                    content.rootElement,
                    seeMoreButton
                ])
            ]),
            Div("comment-controls", [
                start,
                Div("end", [
                    OptionalComponent(user !== null && (user.level === 0 || comment.creatorID === user.ID || user.ID === comment.usersID),
                        Button("delete action", "Remove", async (evt) => {
                            if (isJustForShow !== false) return;
                            if (!(user.level === 0 || comment.creatorID === user.ID || user.ID === comment.usersID)) return;
                            if (!confirm("Are you sure you want to remove comment from: " + comment.username)) return;

                            const response = await AJAX.delete("/comments/" + comment.ID, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);
                            if (response.error) {
                                alert(response.error);
                                return;
                            }

                            if (response.rowCount !== 1) return;

                            const parent = evt.target.closest(".comment");
                            const grandParent = parent.parentElement.closest(".comment");

                            if (grandParent !== null) {
                                const seeReplies = grandParent.querySelector(".see-replies");
                                seeReplies.dataset.count--;

                                if (seeReplies.dataset.count == 0) {
                                    seeReplies.classList.add("display-none");
                                }
                            }


                            parent.remove();
                            context.updateCommentCount(-(parent.getElementsByClassName("comment").length + 1));
                        })
                    ),
                    OptionalComponent(comment.isTopLevel,
                        Button("star-container circular " + (user !== null && comment.creatorID === user.ID && comment.isTopLevel ? "" : "locked"), Div("star"), async evt => {
                            if (isJustForShow !== false) return;
                            if (!(user !== null && comment.creatorID === user.ID && comment.isTopLevel)) return;

                            comment.isPinned = !comment.isPinned;
                            const commentElement = evt.target.closest(".comment");
                            commentElement.classList.toggle("pinned", comment.isPinned);

                            const response = await AJAX.put(`/comments/is-pinned/${comment.ID}/${comment.isPinned ? "pin" : "unpin"}`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

                            if (response.error) {
                                alert(response.error);
                                return;
                            }

                            if (response.rowCount !== 1) {
                                console.log(response);
                                return;
                            }

                            if (context.handlePinned) {
                                context.handlePinned(commentElement);
                            }
                        })
                    )
                ])
            ]),
            yourReply,
            replies
        ], { attributes: { id: comment.ID } })
    );
}

/**
 * @param commentID
 * @param {WCommentSection} context
 * @param container
 * @return {function(*): Promise<HTMLElement>}
 */
function subCommentsLoader(commentID, context, container) {
    return async (index) => {
        const comments = await AJAX.get(`/comments/${context.json.webpageID}/replies/${commentID}/${index}`, JSONHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME);

        let element;
        for (const comment of comments) {
            const commentElement = Comment(comment, context);
            element = commentElement;

            if (document.getElementById(comment.ID) === null) {
                container.appendChild(commentElement);
            }
        }

        return element;
    };
}

/**
 * @param {WCommentSection} context
 * @param parentCommentID
 * @param isJustForShow
 * @return {HTMLElement}
 */
function CommentForm(context, parentCommentID = undefined, isJustForShow = false) {
    const textEditor = WTextEditor.build({
        content: [],
        // mode: "fancy",
        hint: "Write a comment..."
    }, context, true);

    function cancelForm(evt) {
        textEditor.resetContent();

        const commentElement = evt.target.closest(".your-reply");
        if (commentElement !== null) {
            commentElement.classList.add("display-none");
            commentElement.closest(".comment").classList.remove("replying");
        }
    }

    return (
        Div("comment reply", [
            Div("published", [
                Div("profile-picture", [
                    Img(AJAX.SERVER_HOME + "/profile/picture/?width=250&height=250&cropAndScale=true", "pfp")
                ]),
                Div("text-content expand expand-able", [
                    Div("column", [
                        Div("username", [
                            Heading(3, __, user.username),
                            Div("tags", [
                                OptionalComponent(user.level === 0,
                                    Div("admin", "Admin")
                                ),
                                OptionalComponent(context.json.creatorID === user.ID,
                                    Div("creator", "Creator")
                                )
                            ])
                        ])
                    ]),
                    textEditor.rootElement
                ])
            ]),
            Div("comment-controls", [
                Div(),
                Div("end", [
                    Button("action", "Cancel", cancelForm),
                    Button("submit action", "Submit", async (evt) => {
                        if (isJustForShow === true) return;

                        const payload = JSON.stringify(textEditor.save().content);
                        if (payload.length > 2048) {
                            alert("Your message is over " + (payload.length - 2048) + " character" + (payload.length - 2048 !== 1 ? "s" : "") + ". (Styling requires additional characters.)");
                            return;
                        }

                        const comment = await AJAX.post("/comments/", JSONHandler(), AJAX.addCORSHeaders({
                            body: JSON.stringify({
                                parentCommentID,
                                websitesID: context.json.webpageID,
                                content: payload
                            })
                        }), AJAX.SERVER_HOME);

                        if (comment.error) {
                            alert(comment.error);
                            return;
                        }

                        cancelForm(evt);
                        context.updateCommentCount(1);

                        comment.creatorID = context.json.creatorID;
                        comment.isPinned = false;
                        comment.reactionCount = 0;
                        comment.username = user.username;
                        comment.usersID = user.ID;
                        comment.level = user.level;
                        comment.reaction = 0;
                        comment.childrenCount = 0;

                        comment.isTopLevel = parentCommentID === undefined;

                        const commentElement = Comment(comment, context);

                        if (comment.isTopLevel) {
                            context.prependTopLevel(commentElement);
                            return;
                        }

                        let commentsContainer = evt.target.closest(".your-reply");
                        if (commentsContainer === null || commentsContainer.nextElementSibling === null) return;

                        if (commentsContainer.nextElementSibling?.classList?.contains("replies")) {
                            commentsContainer = commentsContainer.nextElementSibling;
                        }

                        const seeReplies = commentsContainer.parentElement.querySelector(".see-replies");
                        seeReplies.classList.remove("display-none");
                        seeReplies.click();
                        seeReplies.dataset.count++;

                        if (commentsContainer.children.length === 0) {
                            commentsContainer.appendChild(commentElement);
                            return;
                        }

                        commentsContainer.insertBefore(commentElement, commentsContainer.children[0]);
                    })
                ])
            ])
        ])
    );
}