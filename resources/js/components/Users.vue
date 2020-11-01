<template>
  <div class="container">
    <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <!-- Table Heading -->
            <h3 class="card-title">Users Table</h3>
            <div class="card-tools">
              <!-- Add New Button -->
              <button class="btn btn-success" @click="newModal">
                Add New <i class="fas fa-user-plus fa-fw"></i>
              </button>

              <!-- <button class="btn btn-success" data-toggle="modal" data-target="#addNew">
                Add New <i class="fas fa-user-plus fa-fw"></i>
              </button> -->
            </div>
          </div>
          <!-- /.card-header -->

          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Registered At</th>
                  <th>Modify</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users.data" :key="user.id">
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.type | upText }}</td>
                  <td>{{ user.created_at | myDate }}</td>
                  <td>
                    <a href="#" @click="editModal(user)">
                      <i class="fa fa-edit blue"></i>
                    </a>
                    /
                    <a href="#" @click="deleteUser(user.id)">
                      <i class="fa fa-trash red"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <pagination
              :data="users"
              @pagination-change-page="getResults"
            ></pagination>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>

    <div v-if="!$gate.isAdminOrAuthor()">
      <not-found></not-found>
    </div>

    <!-- Modal to add and Edit Users -->
    <div
      class="modal fade"
      id="addNew"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addNewLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 v-show="editmode" class="modal-title" id="addNewLabel">
              Update User's Information
            </h5>
            <h5 v-show="!editmode" class="modal-title" id="addNewLabel">
              Add New
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Add New User Form -->
          <form @submit.prevent="editmode ? updateUser() : createUser()">
            <!-- @keydown="form.onKeydown($event)" -->
            <div class="modal-body">
              <div class="form-group">
                <input
                  v-model="form.name"
                  type="text"
                  name="name"
                  placeholder="Name"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('name') }"
                />
                <has-error :form="form" field="name"></has-error>
              </div>

              <div class="form-group">
                <input
                  v-model="form.email"
                  type="email"
                  name="email"
                  placeholder="Email Address"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('email') }"
                />
                <has-error :form="form" field="email"></has-error>
              </div>

              <div class="form-group">
                <textarea
                  v-model="form.bio"
                  type="text"
                  name="bio"
                  id="bio"
                  placeholder="Short bio for user (optional)"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('bio') }"
                >
                </textarea>
                <has-error :form="form" field="bio"></has-error>
              </div>

              <div class="form-group">
                <select
                  name="type"
                  v-model="form.type"
                  id="type"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('type') }"
                >
                  <option value="">Select User Role</option>
                  <option value="admin">Admin</option>
                  <option value="user">Standard User</option>
                  <option value="author">Author</option>
                  <has-error :form="form" field="type"></has-error>
                </select>
              </div>

              <div class="form-group">
                <input
                  v-model="form.password"
                  type="password"
                  name="password"
                  id="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('password') }"
                />
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">
                Close
              </button>
              <button v-show="editmode" type="submit" class="btn btn-success">
                Update
              </button>
              <button v-show="!editmode" type="submit" class="btn btn-primary">
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      editmode: false,
      users: {},
      form: new Form({
        id: "",
        name: "",
        email: "",
        password: "",
        type: "",
        bio: "",
        photo: "",
      }),
    };
  },
  methods: {
    getResults(page = 1) {
      axios.get("api/user?page=" + page).then((response) => {
        this.users = response.data;
      });
    },
    updateUser() {
      //Start Progress Bar
      this.$Progress.start();
      //console.log('Editing data');
      this.form
        .put("api/user/" + this.form.id)
        .then(() => {
          //success
          //Hide Add New User Modal
          $("#addNew").modal("hide");
          swal.fire("Updated!", "Information has been updated.", "success");
          //End Progress Bar
          this.$Progress.finish();
          //refresh table after update
          Fire.$emit("AfterCreate");
        })
        .catch(() => {
          //errors
          //fail Progress Bar
          this.$Progress.fail();
        });
    },
    editModal(user) {
      //set edit mode true
      this.editmode = true;
      //reset the form
      this.form.reset();
      //Show Add New User Modal
      $("#addNew").modal("show");
      //fill the input fileds using user data to edit
      this.form.fill(user);
    },
    newModal() {
      //set edit mode false
      this.editmode = false;
      //reset the form
      this.form.reset();
      //Show Add New User Modal
      $("#addNew").modal("show");
    },
    deleteUser(id) {
      //Show Sweetalert to delete
      swal
        .fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        })
        .then((result) => {
          //Send request to the server to delete
          if (result.isConfirmed) {
            this.form
              .delete("/api/user/" + id)
              .then(() => {
                swal.fire("Deleted!", "Your file has been deleted.", "success");
                //refresh table after delete
                Fire.$emit("AfterCreate");
              })
              .catch(() => {
                swal.fire("Failed!", "THere was something wrong.", "warning");
              });
          }
        });
    },
    loadUsers() {
      if (this.$gate.isAdminOrAuthor()) {
        axios.get("api/user").then(({ data }) => (this.users = data));
      }
    },
    createUser() {
      //Start Progress Bar
      this.$Progress.start();
      this.form
        .post("api/user")
        .then(() => {
          //Refresh Table after create new
          Fire.$emit("AfterCreate");
          //Hide Add New User Modal
          $("#addNew").modal("hide");
          //Show sweetalert
          toast.fire({
            icon: "success",
            title: "User Created successfully",
          });
          // End Sweet Alert
          this.$Progress.finish();
          //End Progress Bar
        })
        .catch(() => {});
    },
  },
  created() {
     Fire.$on('searching',() => {
       let query = this.$parent.search;
       axios.get('api/findUser?q=' + query)
       .then((data) => {
          this.users = data.data
       })
       .catch(() => {

       })
     })
    
    this.loadUsers();
    //update the table in every 3 seconds
    //setInterval(() => this.loadUsers(),3000);
    //Update table after create new
    Fire.$on("AfterCreate", () => {
      this.loadUsers();
    });
  },
};
</script>
