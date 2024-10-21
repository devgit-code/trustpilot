const express = require("express")
const ejs = require("ejs")
const fs = require("fs")
const globals = require("../modules/globals")

module.exports = {
    init(app) {
        const router = express.Router()

        router.get("/:slug", async function (request, result) {
            const slug = request.params.slug || ""

            const blog = await db.collection("blogs")
                .findOne({
                    $and: [{
                        status: "published"
                    }, {
                        slug: slug
                    }]  
                })

            if (blog == null) {
                result.send("Blog not found.")
                return
            }

            // Options for date and time formatting
            const options = {
                // weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                // hour: '2-digit',
                // minute: '2-digit',
                // second: '2-digit',
                // hour12: true
            }

            let blogObj = {
                _id: blog._id,
                title: blog.title || "",
                slug: blog.slug || "",
                featuredImage: blog.featuredImage || "",
                categories: blog.categories || [],
                content: blog.content || "",
                createdAt: (new Date(blog.createdAt)).toLocaleString("en-US", options)
            }

            if (blog.featuredImage && await fs.existsSync(blog.featuredImage)) {
                blogObj.featuredImage = mainURL + "/" + blog.featuredImage
            }

            result.render("blogs/single", {
                blog: blogObj
            })
            return
        })

        router.get("/", async function (request, result) {

            const filter = {
                status: "published"
            }

            // number of records you want to show per page
            const perPage = 10
         
            // total number of records from database
            const total = await db.collection("blogs").countDocuments(filter)
         
            // Calculating number of pagination links required
            const pages = Math.ceil(total / perPage)
         
            // get current page number
            const pageNumber = parseInt(request.query.page || 1)
         
            // get records to skip
            const startFrom = (pageNumber - 1) * perPage

            const blogs = await db.collection("blogs")
                .find(filter)
                .sort({
                    _id: -1
                })
                .skip(startFrom)
                .limit(perPage)
                .toArray()

            const blogsArr = []
            for (let a = 0; a < blogs.length; a++) {

                // Options for date and time formatting
                const options = {
                    // weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    // hour: '2-digit',
                    // minute: '2-digit',
                    // second: '2-digit',
                    // hour12: true
                }

                let blogObj = {
                    _id: blogs[a]._id,
                    title: blogs[a].title || "",
                    slug: blogs[a].slug || "",
                    featuredImage: blogs[a].featuredImage || "",
                    categories: blogs[a].categories || [],
                    createdAt: (new Date(blogs[a].createdAt)).toLocaleString("en-US", options)
                }

                if (blogs[a].featuredImage && await fs.existsSync(blogs[a].featuredImage)) {
                    blogObj.featuredImage = mainURL + "/" + blogs[a].featuredImage
                }

                blogsArr.push(blogObj)
            }

            result.render("blogs/index", {
                blogs: blogsArr,
                pages: pages,
                page: pageNumber
            })
            return
        })

        app.use("/blog", router)
    }
}