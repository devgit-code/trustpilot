const express = require("express")
const { ObjectId } = require("mongodb")
const bcryptjs = require("bcryptjs")
// JWT used for authentication
const jwt = require("jsonwebtoken")
const fs = require("fs")
const { parse } = require("csv-parse")
const nodemailer = require("nodemailer")
const globals = require("./globals")
const companies = require("./companies")
const authAdmin = require("./authAdmin")

module.exports = {

    logoutMessage: "Admin has been logged out. Please login again.",

    init: function (app) {
        var self = this
        const router = express.Router()

        router.post("/deleteCategory", authAdmin, async function (request, result) {
            const admin = request.admin
            const _id = request.fields._id || ""

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "In-valid object ID."
                })
                return
            }

            const categoryObj = await db.collection("categories")
                .findOne({
                    _id: ObjectId(_id)
                })

            if (categoryObj == null) {
                result.json({
                    status: "error",
                    message: "Category not found."
                })
                return
            }

            await db.collection("categories")
                .deleteOne({
                    _id: categoryObj._id
                })

            result.json({
                status: "success",
                message: "Category has been deleted."
            })
            return
        })

        router.post("/editCategory", authAdmin, async function (request, result) {
            const admin = request.admin
            const category = request.fields.category || ""
            const description = request.fields.description || ""
            const _id = request.fields._id || ""

            if (!category || !_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "In-valid object ID."
                })
                return
            }

            const categoryObj = await db.collection("categories")
                .findOne({
                    _id: ObjectId(_id)
                })

            if (categoryObj == null) {
                result.json({
                    status: "error",
                    message: "Category not found."
                })
                return
            }

            await db.collection("categories")
                .findOneAndUpdate({
                    _id: categoryObj._id
                }, {
                    $set: {
                        category: category,
                        description: description,
                        updatedAt: new Date().toUTCString()
                    }
                })

            result.json({
                status: "success",
                message: "Category has been updated."
            })
            return
        })

        router.post("/categories/fetch", authAdmin, async function (request, result) {
            const admin = request.admin
            const _id = request.fields._id || ""

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "In-valid Object ID."
                })
                return
            }

            const categoryObj = await db.collection("categories")
                .findOne({
                    _id: ObjectId(_id)
                })

            if (categoryObj == null) {
                result.json({
                    status: "error",
                    message: "Category not found."
                })
                return
            }

            result.json({
                status: "success",
                message: "Data has been fetched.",
                category: categoryObj
            })
            return
        })

        router.get("/categories/edit/:_id", function (request, result) {
            const _id = request.params._id ?? ""
            result.render("admin/categories/edit", {
                _id: _id
            })
        })

        router.post("/addCategory", authAdmin, async function (request, result) {
            const admin = request.admin
            const category = request.fields.category || ""
            const description = request.fields.description || ""

            if (!category) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            const categoryObj = await db.collection("categories")
                .findOne({
                    category: category
                })

            if (categoryObj != null) {
                result.json({
                    status: "error",
                    message: "Category already exists."
                })
                return
            }

            await db.collection("categories")
                .insertOne({
                    category: category,
                    description: description,
                    createdAt: new Date().toUTCString(),
                    updatedAt: new Date().toUTCString()
                })

            result.json({
                status: "success",
                message: "Category has been added."
            })
            return
        })

        router.get("/categories/add", function (request, result) {
            result.render("admin/categories/add")
        })

        router.post("/fetchCategories", authAdmin, async function (request, result) {
            const search = request.fields.search || ""

            let filter = {}
            if (search != "") {
                filter = {
                    $or: [{
                        category: {
                            $regex: ".*" + search + ".*",
                            $options: "i"
                        }                        
                    }]
                }
            }

            // number of records you want to show per page
            const perPage = 50

            // total number of records from database
            const total = await db.collection("categories").countDocuments(filter)

            // Calculating number of pagination links required
            const pages = Math.ceil(total / perPage)

            // get current page number
            const pageNumber = request.fields.page ?? 1

            // get records to skip
            const startFrom = (pageNumber - 1) * perPage

            const categories = await db.collection("categories")
                .find(filter)
                .sort({
                    _id: -1
                })
                .skip(startFrom)
                .limit(perPage)
                .toArray()

            result.json({
                status: "success",
                message: "Data has been fetched.",
                data: categories,
                pages: pages
            })
            return
        })

        router.get("/categories", function (request, result) {
            result.render("admin/categories/index")
        })

        router.post("/blogs/delete", authAdmin, async function (request, result) {
            var _id = request.fields._id;

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })

                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "Invalid 'ID' value."
                })

                return
            }

            var blog = await db.collection("blogs").findOne({
                _id: ObjectId(_id)
            });
            if (blog == null) {
                result.json({
                    "status": "error",
                    "message": "Blog does not exists."
                });

                return false;
            }

            if (await fs.existsSync(blog.featuredImage)) {
                await fs.unlinkSync(blog.featuredImage)
            }

            await db.collection("blogs").deleteOne({
                _id: blog._id
            });

            result.json({
                "status": "success",
                "message": "Blog has been deleted."
            });
        });

        router.post("/blogs/update", authAdmin, async function (request, result) {
            const _id = request.fields._id || ""
            const title = request.fields.title || ""
            const slug = request.fields.slug || ""
            const excerpt = request.fields.excerpt || ""
            const content = request.fields.content || ""
            const status = request.fields.status || ""
            const featuredImage = request.files.featuredImage
            let categories = request.fields.categories || "[]"
            categories = JSON.parse(categories)

            if (!_id || !title || !slug || !status) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })

                return
            }

            if (!["draft", "published"].includes(status)) {
                result.json({
                    status: "error",
                    message: "Invalid 'status' value."
                })

                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "Invalid 'ID' value."
                })

                return
            }

            if (Array.isArray(featuredImage)) {
                result.json({
                    status: "error",
                    message: "Please select only 1 featured image."
                })

                return
            }

            const blog = await db.collection("blogs").findOne({
                _id: ObjectId(_id)
            })

            if (blog == null) {
                result.json({
                    status: "error",
                    message: "Blog does not exists."
                })

                return
            }

            /*for (let a = 0; a < categories.length; a++) {
                let flag = false
                for (let b = 0; b < globals.categories.length; b++) {
                    if (globals.categories[b].title == categories[a]) {
                        flag = true
                        break
                    }
                }
                if (!flag) {
                    result.json({
                        status: "error",
                        message: "Category '" + categories[a] + "' not found."
                    })

                    return
                }
            }*/

            const updateObj = {
                title: title,
                slug: slug,
                excerpt: excerpt,
                content: content,
                status: status,
                categories: categories
            }

            if (featuredImage && featuredImage.size > 0) {
                const tempType = featuredImage.type.toLowerCase()
                const isImage = (tempType.includes("png") || tempType.includes("jpeg") || tempType.includes("jpg"))
                const isVideo = (tempType.includes("mp4") || tempType.includes("mov") || tempType.includes("flv") || tempType.includes("avi") || tempType.includes("mpeg"))

                if (!isImage && !isVideo) {
                    result.json({
                        status: "error",
                        message: "Please select an image or video for featured."
                    })

                    return
                }

                if (await fs.existsSync(blog.featuredImage)) {
                    await fs.unlinkSync(blog.featuredImage)
                }

                const fileData = await fs.readFileSync(featuredImage.path)
                const fileLocation = "uploads/blogs/" + (new Date().getTime()) + "-" + featuredImage.name
                await fs.writeFileSync(fileLocation, fileData)
                await fs.unlinkSync(featuredImage.path)

                updateObj.featuredImage = fileLocation
            }

            await db.collection("blogs").findOneAndUpdate({
                _id: blog._id
            }, {
                $set: updateObj
            })

            result.json({
                status: "success",
                message: "Data has been saved."
            })
        })

        router.post("/blogs/fetchSingle", authAdmin, async function (request, result) {
            const _id = request.fields._id
            const currentTimestamp = new Date().getTime()

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "Invalid Blog ID."
                })
                return
            }

            const blog = await db.collection("blogs").findOne({
                _id: ObjectId(_id)
            })

            if (blog == null) {
                result.json({
                    status: "error",
                    message: "Blog does not exists."
                })

                return
            }

            const categories = await db.collection("categories")
                .find({})
                .toArray()

            const categoriesArr = []
            for (let a = 0; a < categories.length; a++) {
                categoriesArr.push(categories[a].category)
            }

            if (blog.featuredImage && await fs.existsSync(blog.featuredImage)) {
                blog.featuredImage = mainURL + "/" + blog.featuredImage
            }

            result.json({
                status: "success",
                message: "Data has been fetched.",
                data: blog,
                categories: categoriesArr
            })
        })

        router.get("/blogs/edit/:_id", function (request, result) {
            const _id = request.params._id || ""

            result.render("admin/blogs/edit", {
                _id: _id
            })
        })

        router.post("/blogs/add", authAdmin, async function (request, result) {
            const title = request.fields.title || ""
            const slug = request.fields.slug || ""
            const excerpt = request.fields.excerpt || ""
            const content = request.fields.content || ""
            const status = request.fields.status || ""

            if (!title || !slug || !status) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })

                return
            }

            if (!["draft", "published"].includes(status)) {
                result.json({
                    status: "error",
                    message: "Invalid 'status' value."
                })

                return
            }

            const blog = {
                title: title,
                slug: slug,
                excerpt: excerpt,
                content: content,
                status: status,
                featuredImage: "",
                categories: [],
                createdAt: new Date().toUTCString()
            }

            await db.collection("blogs").insertOne(blog)

            result.json({
                status: "success",
                message: "Data has been saved.",
                id: blog._id
            })
        })

        router.get("/blogs/add", function (request, result) {
            result.render("admin/blogs/add")
        })

        router.post("/blogs/fetch", async function (request, result) {

            var accessToken = request.fields.accessToken;
            var page = parseInt(request.fields.page || 1);

            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });
            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": self.logoutMessage
                });

                return false;
            }

            // number of records you want to show per page
            const perPage = 10
         
            // total number of records from database
            const total = await db.collection("blogs").countDocuments()
         
            // Calculating number of pagination links required
            const pages = Math.ceil(total / perPage)
         
            // get current page number
            const pageNumber = request.fields.page || 1
         
            // get records to skip
            const startFrom = (pageNumber - 1) * perPage

            var blogs = await db.collection("blogs")
                .find({})
                .skip(startFrom)
                .limit(perPage)
                .sort({
                    "_id": -1
                })
                .toArray();

            result.json({
                "status": "success",
                "message": "Data has been fetched.",
                "data": blogs,
                "totalPages": pages
            });
        });

        router.get("/blogs", function (request, result) {
            result.render("admin/blogs/index")
        })

        ////////////////////////////////////////////////////////////////////////////////////

        router.post("/deleteCompany", authAdmin, async function (request, result) {
            const admin = request.admin
            const _id = request.fields._id || ""

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "In-valid object ID."
                })
                return
            }

            const company = await db.collection("companies")
                .findOne({
                    _id: ObjectId(_id)
                })

            if (company == null) {
                result.json({
                    status: "error",
                    message: "Company not found."
                })
                return
            }

            if (company.screenshot && await fs.existsSync(company.screenshot)) {
                await fs.unlinkSync(company.screenshot)
            }

            const reviews = await db.collection("reviews")
                .find({
                    "company._id": company._id
                })
                .toArray()

            for (let a = 0; a < reviews.length; a++) {
                let proofs = reviews[a].proofs || []
                for (let b = 0; b < proofs.length; b++) {
                    if (proofs[b] && await fs.existsSync(proofs[b])) {
                        await fs.unlinkSync(proofs[b])
                    }
                }

                await db.collection("reviews")
                    .deleteOne({
                        _id: reviews[a]._id
                    })
            }

            await db.collection("companies")
                .deleteOne({
                    _id: company._id
                })

            result.json({
                status: "success",
                message: "Company has been deleted."
            })
            return
        })

        router.post("/editCompany", authAdmin, async function (request, result) {
            const admin = request.admin
            const _id = request.fields._id ?? ""
            const title = request.fields.title ?? ""
            const description = request.fields.description ?? ""
            
            let selectedCategories = request.fields.selectedCategories || "[]"
            selectedCategories = JSON.parse(selectedCategories)

            const file = request.files.logo
            const domain = request.fields.domain ?? ""
            const contactPhone = request.fields.contactPhone ?? ""
            const contactEmail = request.fields.contactEmail ?? ""
            const contactCity = request.fields.contactCity ?? ""
            const contactCountry = request.fields.contactCountry ?? ""
            const contactAddress = request.fields.contactAddress ?? ""
            const facebook = request.fields.facebook ?? ""
            const twitter = request.fields.twitter ?? ""
            const instagram = request.fields.instagram ?? ""

            if (!title || !description || !_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "In-valid object ID."
                })
                return
            }

            const company = await db.collection("companies")
                .findOne({
                    _id: ObjectId(_id)
                })

            if (company == null) {
                result.json({
                    status: "error",
                    message: "Company not found."
                })
                return
            }

            let filesArr = []
            if (Array.isArray(file)) {
                for (let a = 0; a < file.length; a++) {
                    const tempType = file[a].type.toLowerCase()
                    if (tempType.includes("image")) {
                        if (file[a].size > 0) {
                            filesArr.push(file[a])
                        }
                    }
                }
            } else {
                const tempType = file.type.toLowerCase()
                if (tempType.includes("image")) {
                    if (file.size > 0) {
                        filesArr.push(file)
                    }
                }
            }

            let fileLocation = company.screenshot
            for (let a = 0; a < filesArr.length; a++) {
                if (await fs.existsSync(fileLocation)) {
                    await fs.unlinkSync(fileLocation)
                }

                const fileData = await fs.readFileSync(filesArr[a].path)
                fileLocation = "uploads/screenshots/" + (new Date().getTime()) + "-" + filesArr[a].name
                await fs.writeFileSync(fileLocation, fileData)
                await fs.unlinkSync(filesArr[a].path)
            }

            await db.collection("companies")
                .findOneAndUpdate({
                    _id: company._id
                }, {
                    $set: {
                        domain: domain ?? title,
                        title: title,
                        description: description,
                        screenshot: fileLocation,
                        categories: selectedCategories,
                        contactPhone: contactPhone,
                        contactEmail: contactEmail,
                        contactCity: contactCity,
                        contactCountry: contactCountry,
                        contactAddress: contactAddress,
                        facebook: facebook,
                        twitter: twitter,
                        instagram: instagram,
                        updatedAt: new Date().toUTCString()
                    }
                })

            result.json({
                status: "success",
                message: "Company has been updated."
            })
            return
        })

        router.get("/companies/edit/:_id", async function (request, result) {
            const _id = request.params._id || ""

            const categories = await db.collection("categories")
                .find({})
                .toArray()

            const categoriesArr = []
            for (let a = 0; a < categories.length; a++) {
                categoriesArr.push(categories[a].category)
            }

            result.render("admin/companies/edit", {
                _id: _id,
                categories: categoriesArr
            })
        })

        router.post("/addCompany", authAdmin, async function (request, result) {
            const admin = request.admin
            const title = request.fields.title ?? ""
            const description = request.fields.description ?? ""
            let selectedCategories = request.fields.selectedCategories || "[]"
            selectedCategories = JSON.parse(selectedCategories)
            const file = request.files.logo
            let domain = request.fields.domain ?? ""
            const contactPhone = request.fields.contactPhone ?? ""
            const contactEmail = request.fields.contactEmail ?? ""
            const contactCity = request.fields.contactCity ?? ""
            const contactCountry = request.fields.contactCountry ?? ""
            const contactAddress = request.fields.contactAddress ?? ""
            const facebook = request.fields.facebook ?? ""
            const twitter = request.fields.twitter ?? ""
            const instagram = request.fields.instagram ?? ""

            if (!title || !description || !file) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            let filesArr = []
            if (Array.isArray(file)) {
                for (let a = 0; a < file.length; a++) {
                    const tempType = file[a].type.toLowerCase()
                    if (tempType.includes("image")) {
                        if (file[a].size > 0) {
                            filesArr.push(file[a])
                        }
                    }
                }
            } else {
                const tempType = file.type.toLowerCase()
                if (tempType.includes("image")) {
                    if (file.size > 0) {
                        filesArr.push(file)
                    }
                }
            }

            if (filesArr.length == 0) {
                result.json({
                    status: "error",
                    message: "Please select an image file for logo."
                })
                return
            }

            domain = domain.split("www.").join("")
            domain = domain.split("http://").join("")
            domain = domain.split("https://").join("")

            const company = await db.collection("companies")
                .findOne({
                    domain: domain
                })

            if (company != null) {
                result.json({
                    status: "error",
                    message: "Domain already exists."
                })
                return
            }

            for (let a = 0; a < filesArr.length; a++) {
                const fileData = await fs.readFileSync(filesArr[a].path)
                const fileLocation = "uploads/screenshots/" + (new Date().getTime()) + "-" + filesArr[a].name
                await fs.writeFileSync(fileLocation, fileData)
                await fs.unlinkSync(filesArr[a].path)

                await db.collection("companies")
                    .insertOne({
                        domain: domain || title,
                        title: title,
                        description: description,
                        keywords: "",
                        author: "",
                        favicons: [],
                        screenshot: fileLocation,
                        categories: selectedCategories,
                        contactPhone: contactPhone,
                        contactEmail: contactEmail,
                        contactCity: contactCity,
                        contactCountry: contactCountry,
                        contactAddress: contactAddress,
                        facebook: facebook,
                        twitter: twitter,
                        instagram: instagram,
                        ratings: 0,
                        reviews: 0,
                        isClaimed: false,
                        createdAt: new Date().toUTCString(),
                        updatedAt: new Date().toUTCString()
                    })

                break
            }

            result.json({
                status: "success",
                message: "Company has been added."
            })
        })

        router.get("/companies/add", async function (request, result) {
            // const categories = []
            // for (let a = 0; a < globals.categories.length; a++) {
            //     categories.push(globals.categories[a].title)
            // }

            const categories = await db.collection("categories")
                .find({})
                .toArray()

            const categoriesArr = []
            for (let a = 0; a < categories.length; a++) {
                categoriesArr.push(categories[a].category)
            }
            
            result.render("admin/companies/add", {
                categories: categoriesArr
            })
        })

        router.post("/fetchCompanies", authAdmin, async function (request, result) {
            const search = request.fields.search || ""

            let filter = {}
            if (search != "") {
                filter = {
                    $or: [{
                        domain: search                        
                    }]
                }
            }

            // number of records you want to show per page
            const perPage = 50

            // total number of records from database
            const total = await db.collection("companies").countDocuments(filter)

            // Calculating number of pagination links required
            const pages = Math.ceil(total / perPage)

            // get current page number
            const pageNumber = request.fields.page ?? 1

            // get records to skip
            const startFrom = (pageNumber - 1) * perPage

            const companies = await db.collection("companies")
                .find(filter)
                .sort({
                    _id: -1
                })
                .skip(startFrom)
                .limit(perPage)
                .toArray()

            for (let a = 0; a < companies.length; a++) {
                if (companies[a].screenshot && await fs.existsSync(companies[a].screenshot)) {
                    companies[a].screenshot = mainURL + "/" + companies[a].screenshot
                }
            }

            result.json({
                status: "success",
                message: "Data has been fetched.",
                data: companies,
                pages: pages
            })
            return
        })

        router.get("/companies", function (request, result) {
            result.render("admin/companies/index")
        })

        router.post("/settings/save", authAdmin, async function (request, result) {
            const accessToken = request.fields.accessToken ?? ""
            const smtp_host = request.fields.smtp_host ?? ""
            const smtp_port = request.fields.smtp_port ?? ""
            const smtp_email = request.fields.smtp_email ?? ""
            const smtp_password = request.fields.smtp_password ?? ""
            const aboutUs = request.fields.aboutUs || ""
            const contactUsEmail = request.fields.contactUsEmail || ""

            const admin = request.admin

            await db.collection("settings")
                .findOneAndUpdate({}, {
                    $set: {
                        smtp: {
                            host: smtp_host,
                            port: smtp_port,
                            email: smtp_email,
                            password: smtp_password
                        },

                        aboutUs: aboutUs,
                        contactUsEmail: contactUsEmail
                    }
                }, {
                    upsert: true
                })

            result.json({
                status: "success",
                message: "Settings has been saved."
            })
            return
        })

        router.get("/settings", async function (request, result) {
            const settings = await db.collection("settings").findOne({})

            const filter = {}

            // number of records you want to show per page
            const perPage = 10
         
            // total number of records from database
            const total = await db.collection("contactUsMessages").countDocuments(filter)
         
            // Calculating number of pagination links required
            const pages = Math.ceil(total / perPage)
         
            // get current page number
            const pageNumber = parseInt(request.query.page || 1)
         
            // get records to skip
            const startFrom = (pageNumber - 1) * perPage

            const contactUsMessages = await db.collection("contactUsMessages")
                .find(filter)
                .sort({
                    _id: -1
                })
                .skip(startFrom)
                .limit(perPage)
                .toArray()

            // Options for date and time formatting
            const options = {
                // weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            }

            const contactUsMessagesArr = []
            for (let a = 0; a < contactUsMessages.length; a++) {
                contactUsMessagesArr.push({
                    _id: contactUsMessages[a]._id,
                    name: contactUsMessages[a].name || "",
                    email: contactUsMessages[a].email || "",
                    message: contactUsMessages[a].message || "",
                    emailId: contactUsMessages[a].emailId || "",
                    createdAt: (new Date(contactUsMessages[a].createdAt)).toLocaleString("en-US", options)
                })
            }

            result.render("admin/settings", {
                settings: settings,
                contactUsMessages: contactUsMessagesArr,
                contactUsMessagesPages: pages,
                mainURL: mainURL
            })
        })

        router.post("/fetchAds", async function (request, result) {
            const accessToken = request.fields.accessToken

            const admin = await db.collection("admins").findOne({
                accessToken: accessToken
            })
            
            if (admin == null) {
                result.json({
                    status: "error",
                    message: "Admin has been logged out. Please login again."
                })
                return
            }

            const ads = await db.collection("advertisements").find({})
                .sort({ createdAt: -1 })
                .toArray()

            result.json({
                status: "success",
                message: "Data has been fetched.",
                ads: ads
            })
        })

        router.get("/ads", async function (request, result) {
            result.render("admin/ads")
        })

        router.post("/users/add", authAdmin, async function (request, result) {
            const name = request.fields.name || ""
            const email = request.fields.email || ""
            let password = request.fields.password || ""

            if (!name || !email || !password) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })

                return
            }

            const salt = await bcryptjs.genSaltSync(10)
            password = await bcryptjs.hashSync(password, salt)

            const userObj = {
                name: name,
                email: email,
                password: password,
                isBanned: false,
                isVerified: true,
                createdAt: new Date().toUTCString()
            }

            await db.collection("users").insertOne(userObj)

            result.json({
                status: "success",
                message: "Data has been saved."
            })
            return
        })

        router.get("/users/add", function (request, result) {
            result.render("admin/users/add")
        })

        router.post("/users/update", authAdmin, async function (request, result) {
            const _id = request.fields._id
            const name = request.fields.name || ""
            const password = request.fields.password || ""

            if (!_id || !name) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })

                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "Invalid User ID."
                })
                return
            }

            const user = await db.collection("users").findOne({
                _id: ObjectId(_id)
            })

            if (user == null) {
                result.json({
                    status: "error",
                    message: "User does not exists."
                })

                return
            }

            const userObj = {
                name: name
            }

            if (password != "") {
                const salt = await bcryptjs.genSaltSync(10)
                userObj.password = await bcryptjs.hashSync(password, salt)
            }

            await db.collection("users").findOneAndUpdate({
                _id: user._id
            }, {
                $set: userObj
            })

            result.json({
                status: "success",
                message: "Data has been saved."
            })
            return
        })

        router.post("/users/fetchSingle", authAdmin, async function (request, result) {
            const _id = request.fields._id
            const currentTimestamp = new Date().getTime()

            if (!_id) {
                result.json({
                    status: "error",
                    message: "Please fill all fields."
                })
                return
            }

            if (!ObjectId.isValid(_id)) {
                result.json({
                    status: "error",
                    message: "Invalid User ID."
                })
                return
            }

            const user = await db.collection("users").findOne({
                _id: ObjectId(_id)
            })

            if (user == null) {
                result.json({
                    status: "error",
                    message: "User does not exists."
                })

                return
            }

            result.json({
                status: "success",
                message: "Data has been fetched.",
                data: user
            })
        })

        router.get("/users/edit/:_id", function (request, result) {
            const _id = request.params._id || ""
            result.render("admin/users/edit", {
                _id: _id
            })
        })

        router.get("/users", function (request, result) {
            result.render("admin/users/index")
        })

        router.post("/users/unban", async function (request, result) {
            var accessToken = request.fields.accessToken;
            var _id = request.fields._id;

            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });
            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": self.logoutMessage
                });

                return false;
            }

            var user = await db.collection("users").findOne({
                "_id": ObjectId(_id)
            });
            if (user == null) {
                result.json({
                    "status": "error",
                    "message": "User does not exists."
                });

                return false;
            }

            await db.collection("users").findOneAndUpdate({
                "_id": ObjectId(_id)
            }, {
                $set: {
                    "isBanned": false
                }
            });

            result.json({
                "status": "success",
                "message": "User has been unbanned."
            });
        });

        router.post("/users/ban", async function (request, result) {
            var accessToken = request.fields.accessToken;
            var _id = request.fields._id;

            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });
            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": self.logoutMessage
                });

                return false;
            }

            var user = await db.collection("users").findOne({
                "_id": ObjectId(_id)
            });
            if (user == null) {
                result.json({
                    "status": "error",
                    "message": "User does not exists."
                });

                return false;
            }

            await db.collection("users").findOneAndUpdate({
                "_id": ObjectId(_id)
            }, {
                $set: {
                    "isBanned": true
                }
            });

            result.json({
                "status": "success",
                "message": "User has been banned."
            });
        });

        router.post("/users/delete", authAdmin, async function (request, result) {
            var accessToken = request.fields.accessToken;
            var _id = request.fields._id;
            const admin = request.admin

            var user = await db.collection("users").findOne({
                "_id": ObjectId(_id)
            });
            if (user == null) {
                result.json({
                    "status": "error",
                    "message": "User does not exists."
                });

                return false;
            }

            if (user.profileImage && await fs.existsSync(user.profileImage)) {
                await fs.unlink(user.profileImage, function (error) {
                    console.log("error deleting file: " + error);
                });
            }

            if (user.coverPhoto && await fs.existsSync(user.coverPhoto)) {
                await fs.unlink(user.coverPhoto, function (error) {
                    console.log("error deleting file: " + error);
                });
            }

            await db.collection("users").deleteOne({
                "_id": ObjectId(_id)
            });

            result.json({
                "status": "success",
                "message": "User has been deleted."
            });
        });

        router.post("/users/fetch", async function (request, result) {

            var accessToken = request.fields.accessToken;
            var skip = parseInt(request.fields.skip);
            var limit = parseInt(request.fields.limit);

            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });
            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": self.logoutMessage
                });

                return false;
            }

            var users = await db.collection("users")
                .find({})
                .skip(skip)
                .limit(limit)
                .sort({
                    "_id": -1
                })
                .toArray();

            for (var a = 0; a < users.length; a++) {
                delete users[a].password;
            }

            var totalPages = await db.collection("users").count() / limit;
            totalPages = Math.ceil(totalPages);

            result.json({
                "status": "success",
                "message": "Data has been fetched.",
                "data": users,
                "totalPages": totalPages
            });
        });

        router.post("/getDashboardData", async function (request, result) {

            var accessToken = request.fields.accessToken;

            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });
            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": self.logoutMessage
                });

                return false;
            }

            var users = await db.collection("users").count();
            
            result.json({
                "status": "success",
                "message": "Data has been fetched.",
                "users": users
            });
        });

        router.get("/", function (request, result) {
            db.collection("admins").findOne({}, function (error, admin) {
                if (!admin) {
                    bcryptjs.genSalt(10, function(err, salt) {
                        bcryptjs.hash(adminPassword, salt, async function(err, hash) {
                            db.collection("admins").insertOne({
                                "email": adminEmail,
                                "password": hash
                            })
                        })
                    })
                }
            });

            result.render("admin/index");
        });

        router.get("/login", function (request, result) {
            result.render("admin/login");
        });

        router.post("/login", async function (request, result) {

            var email = request.fields.email;
            var password = request.fields.password;

            var admin = await db.collection("admins").findOne({
                "email": email
            });

            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": "Email does not exist"
                });

                return false;
            }

            bcryptjs.compare(password, admin.password, async function (error, res) {
                if (res === true) {

                    var accessToken = jwt.sign({ email: email }, accessTokenSecret);
                    await db.collection("admins").findOneAndUpdate({
                        "email": email
                    }, {
                        $set: {
                            "accessToken": accessToken
                        }
                    });

                    result.json({
                        "status": "success",
                        "message": "Login successfully",
                        "accessToken": accessToken
                    });
                    
                } else {
                    result.json({
                        "status": "error",
                        "message": "Password is not correct"
                    });
                }
            });
        });

        router.post("/getAdmin", async function (request, result) {
            var accessToken = request.fields.accessToken;
            
            var admin = await db.collection("admins").findOne({
                "accessToken": accessToken
            });

            if (admin == null) {
                result.json({
                    "status": "error",
                    "message": "User has been logged out. Please login again."
                });

                return false;
            }

            result.json({
                "status": "success",
                "message": "Record has been fetched.",
                "data": admin
            });
        });

        app.use("/admin", router)

    }
};