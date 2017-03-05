/* global module:false */
module.exports = function( grunt ) {
	grunt.initConfig({

		sass: {
			options: {
				style: 'expanded',
				sourceMap: true
			},
			dist: {
				files: {
					'developer-discovery.css': 'sass/developer-discovery.scss',
				}
			}
		},

		watch: {
			sass: {
				files: 'sass/**/*.scss',
				tasks: ['sass']
			}
		},
	});

	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.registerTask( 'default', ['watch'] );
};
