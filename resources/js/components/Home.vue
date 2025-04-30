<template>
  <v-app>
    <v-app-bar app color="primary" dark>
      <v-app-bar-nav-icon></v-app-bar-nav-icon>
      <v-toolbar-title>Todoke Delivery</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn icon>
        <v-icon>mdi-magnify</v-icon>
      </v-btn>
    </v-app-bar>

    <v-main>
      <v-container>
        <v-row>
          <v-col cols="12" md="8">
            <v-card class="pa-4 mb-4">
              <v-card-title class="text-h4">
                Welcome to Todoke
              </v-card-title>
              <v-card-subtitle>
                Hybrid drone & motorbike delivery platform
              </v-card-subtitle>
              <v-card-text>
                <p>Experience the future of delivery with our community-powered pricing model.</p>
                
                <v-btn color="primary" class="mr-2">
                  Get Started
                </v-btn>
                <v-btn color="secondary" outlined>
                  Learn More
                </v-btn>
              </v-card-text>
            </v-card>

            <v-card class="pa-4">
              <v-card-title>Test Form</v-card-title>
              <v-card-text>
                <v-text-field
                  label="Email"
                  placeholder="Enter your email"
                  outlined
                ></v-text-field>
                <v-select
                  :items="['Motorbike', 'Drone', 'Hybrid']"
                  label="Preferred Delivery"
                  outlined
                ></v-select>
                <v-checkbox
                  label="Subscribe to updates"
                ></v-checkbox>
                <v-btn color="success" block>
                  Submit
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="4">
            <v-card class="pa-4">
              <v-card-title>System Status</v-card-title>
              <v-card-text>
                <v-progress-linear
                  color="blue"
                  height="10"
                  value="75"
                  striped
                ></v-progress-linear>
                <p class="mt-2">75% of deliveries on time</p>
                
                <v-chip-group class="mt-4">
                  <v-chip color="green" text-color="white">
                    <v-icon left>mdi-check</v-icon>
                    Operational
                  </v-chip>
                  <v-chip color="orange" text-color="white">
                    <v-icon left>mdi-alert</v-icon>
                    Testing
                  </v-chip>
                </v-chip-group>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: 'HomePage'
}
</script>

<style scoped>
/* Custom styles can be added here */
</style>
